<script setup lang="ts">
defineProps<{ table: { columns: Array<{ name: string; label: string }>; records: Array<Record<string, unknown>>; emptyMessage: string> } }>();
</script>

<template>
    <table>
        <thead><tr><th v-for="column in table.columns" :key="column.name">{{ column.label }}</th></tr></thead>
        <tbody>
            <tr v-if="table.records.length === 0"><td :colspan="table.columns.length">{{ table.emptyMessage }}</td></tr>
            <tr v-for="(record, index) in table.records" v-else :key="index"><td v-for="column in table.columns" :key="column.name">{{ record[column.name] }}</td></tr>
        </tbody>
    </table>
</template>
