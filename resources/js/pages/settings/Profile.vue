<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { LoaderCircle, Unlink, Link2 } from 'lucide-vue-next';
import GoogleController from '@/actions/App/Http/Controllers/GoogleController';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const googleProcessing = ref(false);

function connectGoogleDrive(): void {
    window.location.href = GoogleController.redirect.url();
}

function disconnectGoogleDrive(): void {
    if (googleProcessing.value) {
        return;
    }

    if (!window.confirm('Putuskan koneksi Google Drive?')) {
        return;
    }

    googleProcessing.value = true;

    router.post(GoogleController.disconnect(), {}, {
        preserveScroll: true,
        onFinish: () => {
            googleProcessing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile information"
            description="Update your employee profile and email address"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="nama_instansi">Nama instansi</Label>
                <Input
                    id="nama_instansi"
                    class="mt-1 block w-full"
                    name="nama_instansi"
                    :default-value="user.nama_instansi ?? ''"
                    required
                    autocomplete="organization"
                    placeholder="Nama instansi"
                />
                <InputError class="mt-2" :message="errors.nama_instansi" />
            </div>

            <div class="grid gap-2">
                <Label for="nama">Nama pegawai</Label>
                <Input
                    id="nama"
                    class="mt-1 block w-full"
                    name="nama"
                    :default-value="user.nama ?? user.name"
                    required
                    autocomplete="name"
                    placeholder="Nama lengkap"
                />
                <InputError class="mt-2" :message="errors.nama" />
            </div>

            <div class="grid gap-2">
                <Label for="nip">NIP</Label>
                <Input
                    id="nip"
                    class="mt-1 block w-full"
                    name="nip"
                    :default-value="user.nip ?? ''"
                    required
                    autocomplete="username"
                    placeholder="NIP"
                />
                <InputError class="mt-2" :message="errors.nip" />
            </div>

            <div class="grid gap-2">
                <Label for="jabatan">Jabatan</Label>
                <Input
                    id="jabatan"
                    class="mt-1 block w-full"
                    name="jabatan"
                    :default-value="user.jabatan ?? ''"
                    required
                    placeholder="Jabatan"
                />
                <InputError class="mt-2" :message="errors.jabatan" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email ?? ''"
                    autocomplete="username"
                    placeholder="Email address (optional)"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email && !user.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Your email address is unverified.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Click here to resend the verification email.
                    </Link>
                </p>

                <div
                    v-if="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="google_drive_root">Nama Folder Utama Google Drive</Label>
                <Input
                    id="google_drive_root"
                    class="mt-1 block w-full"
                    name="google_drive_root"
                    :default-value="String(user.google_drive_root ?? 'SKP Laporan')"
                    placeholder="Contoh: SKP 2026"
                />
                <p class="text-[10px] text-muted-foreground">
                    Semua file PDF akan diupload ke dalam folder ini di Google Drive Anda.
                </p>
                <InputError class="mt-2" :message="errors.google_drive_root" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Save</Button
                >
            </div>
        </Form>

        <div class="rounded-xl border border-border/60 bg-card p-5">
            <h2 class="text-sm font-semibold">Google Drive</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Hubungkan akun Google untuk upload PDF hasil kerja dan perilaku kerja per user.
            </p>

            <div class="mt-4 flex items-center gap-3">
                <span
                    v-if="user.google_drive_connected"
                    class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
                >
                    Terhubung
                </span>

                <Button
                    v-if="user.google_drive_connected"
                    type="button"
                    variant="outline"
                    :disabled="googleProcessing"
                    @click="disconnectGoogleDrive"
                >
                    <LoaderCircle v-if="googleProcessing" class="mr-2 size-4 animate-spin" />
                    <Unlink v-else class="mr-2 size-4" />
                    Disconnect Google Drive
                </Button>

                <Button
                    v-else
                    type="button"
                    variant="outline"
                    @click="connectGoogleDrive"
                >
                    <Link2 class="mr-2 size-4" />
                    Connect Google Drive
                </Button>
            </div>
        </div>
    </div>

    <DeleteUser />
</template>
