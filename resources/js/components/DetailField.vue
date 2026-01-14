<template>
    <PanelItem :field="field">
        <template #value>
            <span class="fontawesome-detail-icon">
                <i v-if="field.value" :class="field.value"></i>
            </span>
        </template>
    </PanelItem>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],
};
</script>

<style>
.fontawesome-detail-icon {
    font-size: 1.5em;
}

.fontawesome-detail-icon i {
    display: inline-block;
}

.fontawesome-detail-icon svg {
    width: 1.5em;
    height: 1.5em;
}
</style>
