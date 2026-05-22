<template>
    <div class="relative w-full overflow-auto">

        <table
            class="w-full caption-bottom text-sm">

            <!-- Caption -->
            <caption
                v-if="caption"
                class="mt-4 text-sm text-muted-foreground">

                {{ caption }}
            </caption>

            <!-- Header -->
            <thead class="[&_tr]:border-b">

                <tr>
                    <th
                        v-for="(column, index) in columns"
                        :key="index"
                        class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">

                        {{ column.label }}
                    </th>
                </tr>
            </thead>

            <!-- Body -->
            <tbody class="[&_tr:last-child]:border-0">

                <tr
                    v-for="(row, rowIndex) in rows"
                    :key="rowIndex"
                    class="border-b transition-colors hover:bg-muted/50">

                    <td
                        v-for="(column, colIndex) in columns"
                        :key="colIndex"
                        class="p-2 align-middle">

                        {{ row[column.key] }}
                    </td>
                </tr>
            </tbody>

            <!-- Footer -->
            <tfoot
                v-if="$slots.footer"
                class="border-t bg-muted/50 font-medium">

                <slot name="footer" />
            </tfoot>
        </table>
    </div>
</template>

<script>
export default {
    name: "Table",

    props: {
        columns: {
            type: Array,
            default: () => [],
        },

        rows: {
            type: Array,
            default: () => [],
        },

        caption: {
            type: String,
            default: "",
        },
    },
};
</script>