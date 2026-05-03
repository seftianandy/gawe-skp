<script setup lang="ts">
import IndikatorKinerjaMasterController from '@/actions/App/Http/Controllers/IndikatorKinerjaMasterController';
import IndikatorKinerjaMasterForm from '@/components/indikator-kinerja-master/IndikatorKinerjaMasterForm.vue';
import { dashboard } from '@/routes';
import { index as indikatorKinerjaMasterIndex } from '@/routes/indikator-kinerja-master';

defineProps<{
    indikatorKinerjaMaster: {
        id: number;
        nama_indikator: string;
        satuan: string;
        target: string;
        kategori: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Indikator Kinerja Master', href: indikatorKinerjaMasterIndex() },
            { title: 'Edit', href: indikatorKinerjaMasterIndex() },
        ],
    },
});
</script>

<template>
    <IndikatorKinerjaMasterForm
        title="Edit indikator master"
        description="Perbarui nama indikator, satuan, target, atau kategorinya."
        submit-label="Perbarui indikator"
        :action="IndikatorKinerjaMasterController.update(indikatorKinerjaMaster.id)"
        :initial-values="indikatorKinerjaMaster"
    />
</template>
