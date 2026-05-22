<template>
    <div class="bg-background p-3 w-fit rounded-lg">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4">

            <button
                @click="prevMonth"
                class="h-8 w-8 flex items-center justify-center rounded-md hover:bg-accent transition">

                ◀
            </button>

            <div class="font-medium text-sm">
                {{ currentMonth }} {{ currentYear }}
            </div>

            <button
                @click="nextMonth"
                class="h-8 w-8 flex items-center justify-center rounded-md hover:bg-accent transition">

                ▶
            </button>
        </div>

        <!-- Weekdays -->
        <div class="grid grid-cols-7 gap-1 mb-2 text-center text-xs text-muted-foreground">

            <div
                v-for="day in weekdays"
                :key="day">

                {{ day }}
            </div>
        </div>

        <!-- Days -->
        <div class="grid grid-cols-7 gap-1">

            <!-- Empty spaces -->
            <div
                v-for="n in firstDay"
                :key="'empty-' + n">
            </div>

            <!-- Dates -->
            <button
                v-for="day in daysInMonth"
                :key="day"
                @click="selectDate(day)"
                :class="[
                    'aspect-square rounded-md text-sm flex items-center justify-center transition',
                    selectedDay === day
                        ? 'bg-primary text-primary-foreground'
                        : 'hover:bg-accent'
                ]">

                {{ day }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: "Calendar",

    data() {
        const today = new Date();

        return {
            currentDate: today,
            selectedDay: today.getDate(),

            weekdays: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
        };
    },

    computed: {
        currentMonth() {
            return this.currentDate.toLocaleString("default", {
                month: "long",
            });
        },

        currentYear() {
            return this.currentDate.getFullYear();
        },

        daysInMonth() {
            return new Date(
                this.currentYear,
                this.currentDate.getMonth() + 1,
                0
            ).getDate();
        },

        firstDay() {
            return new Date(
                this.currentYear,
                this.currentDate.getMonth(),
                1
            ).getDay();
        },
    },

    methods: {
        prevMonth() {
            this.currentDate = new Date(
                this.currentYear,
                this.currentDate.getMonth() - 1,
                1
            );
        },

        nextMonth() {
            this.currentDate = new Date(
                this.currentYear,
                this.currentDate.getMonth() + 1,
                1
            );
        },

        selectDate(day) {
            this.selectedDay = day;

            this.$emit(
                "select",
                new Date(
                    this.currentYear,
                    this.currentDate.getMonth(),
                    day
                )
            );
        },
    },
};
</script>