<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { ImagePlus, Trash2 } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { BuktiItem } from '@/types';

type Props = {
    label: string;
    existingPhotos?: BuktiItem[];
    deletedIds?: number[];
    newFiles?: File[];
    error?: string;
};

const props = withDefaults(defineProps<Props>(), {
    existingPhotos: () => [],
    deletedIds: () => [],
    newFiles: () => [],
    error: undefined,
});

const emit = defineEmits<{
    'update:newFiles': [files: File[]];
    'update:deletedIds': [ids: number[]];
}>();

const previews = ref<Array<{ key: string; url: string; name: string }>>([]);

watch(
    () => props.newFiles,
    (files, _, onCleanup) => {
        previews.value.forEach((preview) => URL.revokeObjectURL(preview.url));

        const nextPreviews = files.map((file) => ({
            key: `${file.name}-${file.size}-${file.lastModified}`,
            url: URL.createObjectURL(file),
            name: file.name,
        }));

        previews.value = nextPreviews;

        onCleanup(() => {
            nextPreviews.forEach((preview) => URL.revokeObjectURL(preview.url));
        });
    },
    { immediate: true },
);

function handleChange(event: Event): void {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    emit('update:newFiles', [...props.newFiles, ...files]);
}

function removeNewFile(index: number): void {
    emit(
        'update:newFiles',
        props.newFiles.filter((_, currentIndex) => currentIndex !== index),
    );
}

function toggleExistingPhoto(photoId: number): void {
    emit(
        'update:deletedIds',
        props.deletedIds.includes(photoId)
            ? props.deletedIds.filter((id) => id !== photoId)
            : [...props.deletedIds, photoId],
    );
}
</script>

<template>
    <div class="grid gap-3">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-foreground">{{ label }}</p>
            <label
                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-border bg-muted/40 px-3 py-2 text-sm text-muted-foreground transition hover:border-primary hover:text-primary"
            >
                <ImagePlus class="size-4" />
                Tambah foto
                <input
                    class="hidden"
                    type="file"
                    accept="image/*"
                    multiple
                    @change="handleChange"
                />
            </label>
        </div>

        <div
            v-if="existingPhotos.length || previews.length"
            class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
        >
            <div
                v-for="photo in existingPhotos"
                :key="`existing-${photo.id}`"
                class="overflow-hidden rounded-xl border bg-background"
                :class="
                    deletedIds.includes(photo.id)
                        ? 'border-destructive/60 opacity-50'
                        : 'border-border'
                "
            >
                <img
                    :src="photo.url"
                    alt="Bukti foto"
                    class="h-40 w-full object-cover"
                />
                <div class="flex items-center justify-between gap-2 p-3">
                    <p class="truncate text-xs text-muted-foreground">
                        {{ photo.file_path }}
                    </p>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="toggleExistingPhoto(photo.id)"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </div>
            </div>

            <div
                v-for="(preview, index) in previews"
                :key="preview.key"
                class="overflow-hidden rounded-xl border border-primary/30 bg-background"
            >
                <img
                    :src="preview.url"
                    alt="Preview upload"
                    class="h-40 w-full object-cover"
                />
                <div class="flex items-center justify-between gap-2 p-3">
                    <p class="truncate text-xs text-muted-foreground">
                        {{ preview.name }}
                    </p>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="removeNewFile(index)"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </div>
            </div>
        </div>

        <InputError :message="error" />
    </div>
</template>
