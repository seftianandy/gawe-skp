<script setup lang="ts">
import IndikatorKinerjaMasterController from '@/actions/App/Http/Controllers/IndikatorKinerjaMasterController';
import IndikatorKinerjaMasterForm from '@/components/indikator-kinerja-master/IndikatorKinerjaMasterForm.vue';
import { dashboard } from '@/routes';
import { create as indikatorKinerjaMasterCreate, index as indikatorKinerjaMasterIndex } from '@/routes/indikator-kinerja-master';

defineProps<{
    form: {
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
            { title: 'Buat', href: indikatorKinerjaMasterCreate() },
        ],
    },
});
</script>

<template>
    <IndikatorKinerjaMasterForm
        title="Buat indikator master"
        description="Tambahkan indikator yang nantinya bisa dipilih ulang saat membuat hasil kerja."
        submit-label="Simpan indikator"
        :action="IndikatorKinerjaMasterController.store()"
        :initial-values="form"
    />
</template>
