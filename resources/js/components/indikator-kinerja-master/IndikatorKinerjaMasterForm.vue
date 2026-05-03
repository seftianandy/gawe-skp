<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type RouteAction = {
    method: 'get' | 'post' | 'put' | 'patch' | 'delete';
    url: string;
};

type FormValues = {
    nama_indikator: string;
    satuan: string;
    target: string;
    kategori: string;
};

type Props = {
    title: string;
    description: string;
    submitLabel: string;
    action: RouteAction;
    initialValues: FormValues;
};

const props = defineProps<Props>();

const form = useForm({
    nama_indikator: props.initialValues.nama_indikator,
    satuan: props.initialValues.satuan,
    target: props.initialValues.target,
    kategori: props.initialValues.kategori,
});

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
                <CardTitle>Form indikator master</CardTitle>
            </CardHeader>
            <CardContent>
                <form class="grid gap-5" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="nama_indikator">Nama indikator</Label>
                        <textarea
                            id="nama_indikator"
                            v-model="form.nama_indikator"
                            rows="4"
                            class="min-h-28 rounded-xl border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none ring-0 transition placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                            placeholder="Contoh: Penyusunan laporan bulanan tepat waktu"
                        />
                        <InputError :message="form.errors.nama_indikator" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="satuan">Satuan</Label>
                            <Input id="satuan" v-model="form.satuan" placeholder="Contoh: dokumen" />
                            <InputError :message="form.errors.satuan" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="target">Target</Label>
                            <Input id="target" v-model="form.target" placeholder="Contoh: 1" />
                            <InputError :message="form.errors.target" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="kategori">Kategori</Label>
                            <Input id="kategori" v-model="form.kategori" placeholder="Contoh: kualitas" />
                            <InputError :message="form.errors.kategori" />
                        </div>
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
