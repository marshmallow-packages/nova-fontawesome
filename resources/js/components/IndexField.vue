<template>
    <span
        v-if="hasValue"
        class="fontawesome-index-icon inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-2 border border-gray text-gray-700 dark:text-gray-200"
        style="width: 2.5rem; height: 2.5rem"
    >
        <i :class="fieldValue" style="font-size: 1.25rem"></i>
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

    .fontawesome-index-icon svg {
        width: 1rem;
        height: 1rem;
    }
</style>
