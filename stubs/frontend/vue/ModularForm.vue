<script setup lang="ts">
defineProps<{ form: { fields: Array<{ name: string; label: string; type: string; required: boolean; options?: Record<string, string> }> ; values: Record<string, unknown> } }>();
</script>

<template>
    <form>
        <label v-for="field in form.fields" :key="field.name">
            {{ field.label }}
            <select v-if="field.type === 'select'" :name="field.name">
                <option v-for="(label, value) in field.options" :key="value" :value="value">{{ label }}</option>
            </select>
            <input v-else :name="field.name" :type="field.type" :value="form.values[field.name]" :required="field.required">
        </label>
        <button type="submit">Submit</button>
    </form>
</template>
