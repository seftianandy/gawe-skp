<script setup lang="ts">
import LaporanController from '@/actions/App/Http/Controllers/LaporanController';
import LaporanForm from '@/components/laporan/LaporanForm.vue';
import { dashboard } from '@/routes';
import { index as laporanIndex } from '@/routes/laporan';
import type { SelectOption } from '@/types';

defineProps<{
    laporan: {
        id: number;
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
            { title: 'Edit', href: laporanIndex() },
        ],
    },
});
</script>

<template>
    <LaporanForm
        title="Edit laporan SKP"
        description="Perbarui periode dan status laporan. Validasi backend akan menjaga unik per bulan."
        submit-label="Perbarui laporan"
        :action="LaporanController.update(laporan.id)"
        :initial-values="laporan"
        :status-options="statusOptions"
    />
</template>
