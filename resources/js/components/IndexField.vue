<template>
    <span
        v-if="hasValue"
        class="fontawesome-index-icon inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-1 border border-gray text-gray-700 dark:text-gray-200"
        style="width: 2rem; height: 2rem; font-size: 1rem"
    >
        <i :class="fieldValue" style="font-size: 1rem"></i>
    </span>
    <span v-else>&mdash;</span>
</template>

<script>
    export default {
        props: ["resourceName", "field"],
        computed: {
            fieldValue() {
                return this.field?.value || this.field?.displayedAs || "";
            },
            hasValue() {
                return !!this.fieldValue;
            },
        },
    };
</script>

<style>
    .border-gray {
        border-color: rgb(var(--colors-gray-300));
    }

    .dark .border-gray {
        border-color: rgb(var(--colors-gray-700));
    }
</style>
