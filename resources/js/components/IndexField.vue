<template>
    <span
        class="fontawesome-index-icon inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-1 border border-gray text-gray-700 dark:text-gray-200"
        style="width: 2rem; height: 2rem; font-size: 1rem"
    >
        <i v-if="field.value" :class="field.value" style="font-size: 1rem"></i>
    </span>
</template>

<script>
    export default {
        props: ["resourceName", "field"],
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
