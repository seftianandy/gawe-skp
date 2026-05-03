<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import type { SelectOption } from '@/types';

// Tambahkan daftar bulan
const daftarBulan = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// Fungsi untuk mendapatkan nama bulan berdasarkan angka (1-12)
const namaBulanTerpilih = computed(() => {
    return daftarBulan[form.bulan - 1] || '-';
});

type RouteAction = {
    method: 'get' | 'post' | 'put' | 'patch' | 'delete';
    url: string;
};

type Props = {
    title: string;
    description: string;
    submitLabel: string;
    action: RouteAction;
    initialValues: {
        periode: string;
        bulan: number;
        tahun: number;
        status: string;
    };
    statusOptions: SelectOption[];
};

const props = defineProps<Props>();

const form = useForm({
    periode: props.initialValues.periode,
    bulan: props.initialValues.bulan,
    tahun: props.initialValues.tahun,
    status: props.initialValues.status,
});

watch(
    () => form.periode,
    (value) => {
        if (!value) {
            return;
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return;
        }

        form.bulan = date.getMonth() + 1;
        form.tahun = date.getFullYear();
    },
    { immediate: true },
);

const periodeLabel = computed(() =>
    form.periode ? new Date(form.periode).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '-',
);

function submit(): void {
    form.submit(props.action.method, props.action.url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex max-w-3xl flex-col gap-6 p-4 md:p-6">
        <Heading :title="title" :description="description" />

        <Card class="border-border/70">
            <CardHeader>
                <CardTitle>Form laporan</CardTitle>
            </CardHeader>
            <CardContent>
                <form class="grid gap-6" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="periode">Periode</Label>
                        <Input id="periode" v-model="form.periode" type="date" />
                        <InputError :message="form.errors.periode" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="bulan">Bulan</Label>
                            <!-- Tampilkan nama bulan, tapi value di form tetap angka -->
                            <Input
                                id="bulan"
                                :model-value="namaBulanTerpilih"
                                readonly
                                class="bg-muted/50 font-medium"
                            />
                            <InputError :message="form.errors.bulan" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="tahun">Tahun</Label>
                            <Input
                                id="tahun"
                                :model-value="String(form.tahun)"
                                readonly
                                class="bg-muted/50 font-medium"
                            />
                            <InputError :message="form.errors.tahun" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="rounded-2xl border border-dashed border-primary/30 bg-primary/5 p-4 text-sm text-muted-foreground">
                        Periode aktif: <span class="font-medium text-foreground">{{ periodeLabel }}</span>.
                        Validasi backend tetap akan memastikan satu user hanya memiliki satu laporan per bulan.
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <Button type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            {{ submitLabel }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
