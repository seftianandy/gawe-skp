export type User = {
    id: number;
    name: string;
    nama_instansi: string | null;
    nama: string | null;
    nip: string | null;
    jabatan: string | null;
    email: string | null;
    google_id?: string | null;
    google_drive_connected?: boolean;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
