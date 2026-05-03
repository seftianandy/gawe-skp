<script setup lang="ts">
import LaporanController from '@/actions/App/Http/Controllers/LaporanController';
import LaporanForm from '@/components/laporan/LaporanForm.vue';
import { dashboard } from '@/routes';
import { create as laporanCreate, index as laporanIndex } from '@/routes/laporan';
import type { SelectOption } from '@/types';

defineProps<{
    form: {
        periode: string;
        bulan: number;
        tahun: number;
        status: string;
    };
    statusOptions: SelectOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Laporan', href: laporanIndex() },
            { title: 'Buat', href: laporanCreate() },
        ],
    },
});
</script>

<template>
    <LaporanForm
        title="Buat laporan SKP"
        description="Isi periode dan status awal. Setelah dibuat, detail hasil kerja dapat dilengkapi dari halaman detail."
        submit-label="Simpan laporan"
        :action="LaporanController.store()"
        :initial-values="form"
        :status-options="statusOptions"
    />
</template>
