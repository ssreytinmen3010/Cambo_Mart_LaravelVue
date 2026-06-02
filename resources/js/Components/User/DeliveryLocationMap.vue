<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

const props = defineProps({
    lat: { type: Number, default: null },
    lng: { type: Number, default: null },
    storeLat: { type: Number, required: true },
    storeLng: { type: Number, required: true },
    storeName: { type: String, default: 'Store' },
    deliveryLabel: { type: String, default: 'Your address' },
});

const emit = defineEmits(['pick']);

const mapRoot = ref(null);
let map = null;
let deliveryMarker = null;
let storeMarker = null;
let routeLayer = null;
let routeRequestId = 0;
let routeDebounceTimer = null;

const deliveryIcon = L.icon({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
});

function createLabeledIcon(label, variant) {
    return L.divIcon({
        className: '',
        html: `<span class="delivery-map-pin delivery-map-pin--${variant}">${label}</span>`,
        iconSize: [56, 28],
        iconAnchor: [28, 14],
    });
}

function emitPick(latlng) {
    emit('pick', { lat: latlng.lat, lng: latlng.lng });
}

function deliveryTooltipText() {
    const label = props.deliveryLabel?.trim();

    return label ? `To: ${label}` : 'To: Your address';
}

function removeRoute() {
    if (routeLayer && map) {
        map.removeLayer(routeLayer);
        routeLayer = null;
    }
}

function drawRoute(latlngs, { dashed = false } = {}) {
    if (!map) {
        return;
    }

    removeRoute();

    routeLayer = L.polyline(latlngs, {
        color: dashed ? '#f87171' : '#dc2626',
        weight: dashed ? 3 : 5,
        opacity: dashed ? 0.8 : 0.9,
        dashArray: dashed ? '10 8' : null,
        lineCap: 'round',
        lineJoin: 'round',
    }).addTo(map);

    routeLayer.bringToBack();
}

async function updateRoute() {
    if (!map || props.lat == null || props.lng == null) {
        removeRoute();
        return;
    }

    const requestId = ++routeRequestId;
    const from = `${props.storeLng},${props.storeLat}`;
    const to = `${props.lng},${props.lat}`;
    const fallback = [
        [props.storeLat, props.storeLng],
        [props.lat, props.lng],
    ];

    try {
        const response = await fetch(
            `https://router.project-osrm.org/route/v1/driving/${from};${to}?overview=full&geometries=geojson`,
        );

        if (requestId !== routeRequestId) {
            return;
        }

        const data = await response.json();
        const geometry = data?.routes?.[0]?.geometry?.coordinates;

        if (data?.code === 'Ok' && Array.isArray(geometry) && geometry.length > 1) {
            drawRoute(geometry.map(([lng, lat]) => [lat, lng]));
            return;
        }
    } catch {
        // Fall through to straight-line route.
    }

    if (requestId === routeRequestId) {
        drawRoute(fallback, { dashed: true });
    }
}

function scheduleRouteUpdate() {
    if (routeDebounceTimer) {
        window.clearTimeout(routeDebounceTimer);
    }

    routeDebounceTimer = window.setTimeout(() => {
        void updateRoute();
    }, 350);
}

function setDeliveryMarker(lat, lng, { emitEvent = false } = {}) {
    if (!map) {
        return;
    }

    const latlng = L.latLng(lat, lng);

    if (deliveryMarker) {
        deliveryMarker.setLatLng(latlng);
    } else {
        deliveryMarker = L.marker(latlng, {
            draggable: true,
            icon: deliveryIcon,
            zIndexOffset: 1000,
        }).addTo(map);
        deliveryMarker.on('dragend', () => {
            emitPick(deliveryMarker.getLatLng());
            scheduleRouteUpdate();
        });
    }

    deliveryMarker
        .bindTooltip(deliveryTooltipText(), {
            permanent: true,
            direction: 'top',
            className: 'delivery-map-tooltip',
        })
        .openTooltip();

    if (emitEvent) {
        emitPick(latlng);
    }

    scheduleRouteUpdate();
}

function clearDeliveryMarker() {
    if (deliveryMarker && map) {
        map.removeLayer(deliveryMarker);
        deliveryMarker = null;
    }

    removeRoute();
}

function fitMapView() {
    if (!map) {
        return;
    }

    if (props.lat != null && props.lng != null) {
        const bounds = L.latLngBounds(
            [props.storeLat, props.storeLng],
            [props.lat, props.lng],
        );

        map.fitBounds(bounds.pad(0.3), { maxZoom: 15, animate: false });
        return;
    }

    map.setView([props.storeLat, props.storeLng], 13, { animate: false });
}

function refreshStoreMarker() {
    if (!map) {
        return;
    }

    const latlng = [props.storeLat, props.storeLng];
    const label = 'From';

    if (storeMarker) {
        storeMarker.setLatLng(latlng);
        storeMarker.setTooltipContent(`From: ${props.storeName}`);
    } else {
        storeMarker = L.marker(latlng, {
            icon: createLabeledIcon(label, 'from'),
            zIndexOffset: 500,
        })
            .addTo(map)
            .bindTooltip(`From: ${props.storeName}`, {
                permanent: true,
                direction: 'top',
                className: 'delivery-map-tooltip delivery-map-tooltip--from',
            })
            .openTooltip();
    }
}

onMounted(() => {
    map = L.map(mapRoot.value, {
        scrollWheelZoom: true,
        zoomControl: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    refreshStoreMarker();

    map.on('click', (event) => {
        setDeliveryMarker(event.latlng.lat, event.latlng.lng, { emitEvent: true });
    });

    if (props.lat != null && props.lng != null) {
        setDeliveryMarker(props.lat, props.lng);
    }

    fitMapView();

    requestAnimationFrame(() => map?.invalidateSize());
});

watch(
    () => [props.lat, props.lng],
    ([lat, lng], previous) => {
        if (lat == null || lng == null) {
            clearDeliveryMarker();
            fitMapView();
            return;
        }

        const [prevLat, prevLng] = previous ?? [];

        if (prevLat === lat && prevLng === lng) {
            return;
        }

        setDeliveryMarker(lat, lng);
        fitMapView();
    },
);

watch(
    () => [props.storeLat, props.storeLng, props.storeName],
    () => {
        refreshStoreMarker();
        scheduleRouteUpdate();
        fitMapView();
    },
);

watch(
    () => props.deliveryLabel,
    () => {
        if (deliveryMarker) {
            deliveryMarker.setTooltipContent(deliveryTooltipText());
        }
    },
);

onBeforeUnmount(() => {
    if (routeDebounceTimer) {
        window.clearTimeout(routeDebounceTimer);
    }

    map?.remove();
    map = null;
    deliveryMarker = null;
    storeMarker = null;
    routeLayer = null;
});
</script>

<template>
    <div class="delivery-location-map-wrap">
        <div
            ref="mapRoot"
            class="delivery-location-map h-72 w-full"
            role="application"
            aria-label="Delivery route map"
        />
        <div
            v-if="lat != null && lng != null"
            class="delivery-location-map__legend"
        >
            <span class="delivery-location-map__legend-item delivery-location-map__legend-item--from">
                <span class="delivery-location-map__dot delivery-location-map__dot--from" />
                From: {{ storeName }}
            </span>
            <span class="delivery-location-map__legend-item delivery-location-map__legend-item--to">
                <span class="delivery-location-map__dot delivery-location-map__dot--to" />
                To: {{ deliveryLabel || 'Your address' }}
            </span>
            <span class="delivery-location-map__legend-route">— route —</span>
        </div>
    </div>
</template>

<style>
.delivery-location-map-wrap {
    position: relative;
}

.delivery-location-map {
    z-index: 0;
}

.delivery-location-map__legend {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem 1rem;
    border-top: 1px solid hsl(var(--border) / 0.6);
    background: hsl(var(--background));
    padding: 0.5rem 1rem;
    font-size: 0.6875rem;
    color: hsl(var(--muted-foreground));
}

.delivery-location-map__legend-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 600;
    color: hsl(var(--foreground));
}

.delivery-location-map__legend-route {
    font-size: 0.625rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #dc2626;
}

.delivery-location-map__dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 9999px;
    border: 2px solid #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.delivery-location-map__dot--from {
    background: hsl(var(--primary));
}

.delivery-location-map__dot--to {
    background: #dc2626;
}

.delivery-map-pin {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.2rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: #fff;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    white-space: nowrap;
}

.delivery-map-pin--from {
    background: hsl(var(--primary));
}

.delivery-map-tooltip {
    border: none !important;
    background: #dc2626 !important;
    color: #fff !important;
    font-size: 0.625rem !important;
    font-weight: 700 !important;
    padding: 0.2rem 0.45rem !important;
    border-radius: 0.375rem !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

.delivery-map-tooltip--from {
    background: hsl(var(--primary)) !important;
}

.delivery-map-tooltip::before {
    border-top-color: #dc2626 !important;
}

.delivery-map-tooltip--from::before {
    border-top-color: hsl(var(--primary)) !important;
}
</style>
