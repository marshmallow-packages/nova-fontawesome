<template>
    <PanelItem :field="field">
        <template #value>
            <span
                class="fontawesome-detail-icon relative inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-1 border border-gray"
                style="width: 2.5rem; height: 2.5rem"
            >
                <i v-if="field.value" :class="field.value"></i>
            </span>
        </template>
    </PanelItem>
</template>

<script>
    export default {
        props: ["resource", "resourceName", "resourceId", "field"],
    };
</script>

<style>
    .fontawesome-detail-icon {
        font-size: 1.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .fontawesome-detail-icon i {
        font-size: 1.5rem;
        max-width: 100%;
        max-height: 100%;
    }

    .detail-icon-svg {
        display: flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        max-height: 100%;
    }

    .detail-icon-svg :deep(svg) {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        fill: currentColor;
    }

    .detail-icon-svg svg {
        fill: currentColor;
    }

    .skeleton-box {
        display: inline-block;
    }

    @keyframes pulse {
        0%,
        100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
