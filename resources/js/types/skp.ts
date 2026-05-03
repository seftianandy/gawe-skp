export type SelectOption = {
    label: string;
    value: string;
};

export type IndikatorKinerjaMasterItem = {
    id: number;
    nama_indikator: string;
    satuan: string;
    target: string;
    kategori: string;
};

export type BuktiItem = {
    id: number;
    hasil_kerja_id?: number | null;
    file_path: string;
    url: string;
};

export type RealisasiForm = {
    id: number | null;
    tanggal: string;
    output: string;
    keterangan: string;
    bukti_foto: BuktiItem[];
    bukti_foto_baru: File[];
    hapus_bukti_foto: number[];
};

export type RencanaAksiForm = {
    id: number | null;
    hasil_kerja_id?: number | null;
    deskripsi: string;
};

export type IndikatorForm = {
    id: number | null;
    deskripsi: string;
    satuan: string;
    target: string;
    kategori: string;
    rencana_aksi: RencanaAksiForm[];
    realisasi: RealisasiForm[];
};

export type HasilKerjaForm = {
    id: number | null;
    indikator_kinerja_id: number | string | null;
    isi_ai?: string | null;
    indikator_kinerja_master?: IndikatorKinerjaMasterItem | null;
    rencana_aksi: RencanaAksiForm[];
    bukti_foto: BuktiItem[];
    bukti_foto_baru: File[];
    hapus_bukti_foto: number[];
    indikators: IndikatorForm[];
};

export type PerilakuKerjaForm = {
    id: number | null;
    nama: string;
    deskripsi: string;
    isi_ai?: string | null;
    bukti_perilaku: BuktiItem[];
    bukti_perilaku_baru: File[];
    hapus_bukti_perilaku: number[];
};

export type LaporanListItem = {
    id: number;
    periode: string;
    periode_label: string;
    bulan: number;
    tahun: number;
    status: string;
    hasil_kerja_count: number;
    perilaku_kerja_count: number;
    isi_laporan: string | null;
};

export type LaporanDetail = {
    id: number;
    periode: string;
    periode_label: string;
    bulan: number;
    tahun: number;
    status: string;
    isi_laporan: string | null;
    file_pdf: string | null;
    user: {
        nama: string | null;
        nama_instansi: string | null;
        jabatan: string | null;
        nip: string | null;
        email: string | null;
    };
    hasil_kerja: HasilKerjaForm[];
    perilaku_kerja: PerilakuKerjaForm[];
};
