<template>
    <PanelItem :field="field">
        <template #value>
            <div v-if="field.value" class="flex items-center gap-4">
                <span
                    class="fontawesome-detail-icon relative inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-2 border border-gray"
                    style="width: 3rem; height: 3rem"
                >
                    <i :class="field.value" style="font-size: 1.5rem"></i>
                </span>
                <div class="icon-info text-sm">
                    <div class="font-medium text-gray-700 dark:text-gray-300">{{ iconName }}</div>
                    <div class="text-gray-500 dark:text-gray-400 text-xs">{{ iconFamily }} / {{ iconStyle }}</div>
                </div>
            </div>
            <span v-else>&mdash;</span>
        </template>
    </PanelItem>
</template>

<script>
    export default {
        props: ["resource", "resourceName", "resourceId", "field"],
        computed: {
            iconName() {
                if (!this.field.value) return '';
                const match = this.field.value.match(/fa-([a-z0-9-]+)$/i);
                return match ? match[1] : this.field.value;
            },
            iconFamily() {
                if (!this.field.value) return '';
                if (this.field.value.includes('fa-brands')) return 'brands';
                if (this.field.value.includes('fa-sharp-duotone')) return 'sharp-duotone';
                if (this.field.value.includes('fa-sharp')) return 'sharp';
                if (this.field.value.includes('fa-duotone')) return 'duotone';
                return 'classic';
            },
            iconStyle() {
                if (!this.field.value) return '';
                if (this.field.value.includes('fa-brands')) return 'brands';
                if (this.field.value.includes('fa-solid')) return 'solid';
                if (this.field.value.includes('fa-regular')) return 'regular';
                if (this.field.value.includes('fa-light')) return 'light';
                if (this.field.value.includes('fa-thin')) return 'thin';
                if (this.field.value.includes('fa-duotone')) return 'duotone';
                return 'solid';
            },
        },
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
