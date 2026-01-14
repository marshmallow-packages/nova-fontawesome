<template>
    <span v-if="hasValue" class="fontawesome-index-icon">
        <i :class="fieldValue"></i>
    </span>
    <span v-else>&mdash;</span>
</template>

<script>
export default {
    props: ['resourceName', 'field'],
    computed: {
        fieldValue() {
            return this.field?.value || this.field?.displayedAs || '';
        },
        hasValue() {
            return !!this.fieldValue;
        },
    },
};
</script>

<style>
.fontawesome-index-icon {
    font-size: 1.5em;
}

.fontawesome-index-icon i {
    display: inline-block;
}

.fontawesome-index-icon svg {
    width: 1.5em;
    height: 1.5em;
}
</style>
