// notifier.js
document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get("msg");
    const obj = params.get("obj");

    const notifications = {
        success: {
            icon: "success",
            title: "Berhasil",
            text: (obj) =>
                `Operasi pada ${getObjName(obj)} berhasil dilakukan.`,
        },
        added: {
            icon: "success",
            title: "Sukses",
            text: (obj) =>
                `${capitalize(getObjName(obj))} berhasil ditambahkan.`,
        },
        updated: {
            icon: "success",
            title: "Diperbarui",
            text: (obj) =>
                `${capitalize(getObjName(obj))} berhasil diperbarui.`,
        },
        deleted: {
            icon: "success",
            title: "Terhapus",
            text: (obj) => `${capitalize(getObjName(obj))} berhasil dihapus.`,
        },
        error: {
            icon: "error",
            title: "Kesalahan",
            text: (obj) =>
                `Terjadi kesalahan saat memproses ${getObjName(obj)}.`,
        },
        failed: {
            icon: "error",
            title: "Gagal",
            text: (obj) =>
                `Tidak dapat menyimpan ${getObjName(obj)} ke database.`,
        },
        kosong: {
            icon: "warning",
            title: "Form Tidak Lengkap",
            text: (obj) => getKosongText(obj),
        },
        duplicate: {
            icon: "warning",
            title: "Duplikat Data",
            text: (obj) => getDuplicateText(obj),
        },
        duplikat: {
            icon: "warning",
            title: "Data Duplikat",
            text: (obj) => getDuplicateText(obj),
        },
        invalid: {
            icon: "warning",
            title: "Tidak Valid",
            text: (obj) => getInvalidText(obj),
        },
        not_found: {
            icon: "error",
            title: "Tidak Ditemukan",
            text: (obj) =>
                `${capitalize(
                    getObjName(obj)
                )} tidak ditemukan atau tidak tersedia.`,
        },
        upload_success: {
            icon: "success",
            title: "Upload Berhasil",
            text: (obj) => `Berkas untuk ${getObjName(obj)} berhasil diunggah.`,
        },
        upload_error: {
            icon: "error",
            title: "Upload Gagal",
            text: (obj) =>
                `Gagal mengunggah berkas untuk ${getObjName(
                    obj
                )}. Pastikan format dan ukuran file sesuai.`,
        },
        fk_blocked: {
            icon: "error",
            title: "Tidak Bisa Dihapus",
            text: (obj) => getFKBlockedText(obj),
        },
        unauthorized: {
            icon: "error",
            title: "Akses Ditolak",
            text: (obj) => getUnauthorizedText(obj),
        },
        nochange: {
            icon: "info",
            title: "Tidak Ada Perubahan",
            text: (obj) => getNochangeText(obj),
        },
    };

    if (msg && notifications[msg]) {
        const notif = notifications[msg];
        Swal.fire({
            icon: notif.icon,
            title: notif.title,
            text:
                typeof notif.text === "function" ? notif.text(obj) : notif.text,
            timer: 2000,
            showConfirmButton: false,
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    }
});

// Mapping nama objek ke nama tampilannya
function getObjName(obj) {
    const map = {
        user: "user",
        jadwalrapat: "jadwal rapat",
        undangan: "undangan rapat",
        kehadiran: "kehadiran rapat",
        dok_rapat: "dokumen rapat",
        notulen: "notulen",
        diskusi: "diskusi",
        dokumen_usulan: "dokumen usulan",
        arsiprapat: "arsip rapat",
        dokumentasi: "dokumentasi kegiatan",
        penyerahandok: "penyerahan dokumen",
        laporan: "laporan",
    };
    return map[obj] || "data";
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// === Global handler untuk seluruh modul Raperda ===

function getDuplicateText(obj) {
    const map = {
        user: "Username sudah digunakan.",
        jadwalrapat: "Jadwal rapat ini sudah terdaftar.",
        undangan: "Undangan untuk rapat ini sudah dibuat.",
        kehadiran: "Data kehadiran sudah tercatat.",
        dok_rapat: "Dokumen rapat sudah pernah diunggah.",
        notulen: "Notulen rapat sudah tersedia.",
        diskusi: "Topik diskusi sudah ada.",
        dokumen_usulan: "Usulan untuk rapat ini sudah ada.",
        arsiprapat: "Arsip rapat sudah tersedia.",
        dokumentasi: "Dokumentasi sudah diunggah.",
        penyerahandok: "Dokumen sudah diserahkan sebelumnya.",
        laporan: "Laporan untuk modul ini sudah dibuat.",
    };
    return map[obj] || "Data yang Anda masukkan sudah ada.";
}

function getKosongText(obj) {
    const map = {
        user: "Username dan password wajib diisi.",
        jadwalrapat: "Field jadwal rapat tidak boleh kosong.",
        undangan: "Lengkapi data undangan rapat.",
        kehadiran: "Status kehadiran belum dipilih.",
        dok_rapat: "Dokumen dan deskripsi wajib diisi.",
        notulen: "Isi notulen rapat belum tersedia.",
        diskusi: "Topik atau komentar diskusi belum diisi.",
        dokumen_usulan: "Lengkapi dokumen dan judul rapat.",
        arsiprapat: "Field arsip rapat belum lengkap.",
        dokumentasi: "Foto kegiatan dan deskripsi wajib diisi.",
        penyerahandok: "Data penyerahan belum lengkap.",
        laporan: "Filter laporan harus dipilih terlebih dahulu.",
    };
    return map[obj] || "Harap lengkapi semua field yang dibutuhkan.";
}

function getInvalidText(obj) {
    const map = {
        user: "Data user tidak ditemukan.",
        jadwalrapat: "Jadwal rapat tidak valid.",
        undangan: "Data undangan tidak ditemukan.",
        kehadiran: "Data kehadiran tidak valid.",
        dok_rapat: "Dokumen tidak ditemukan.",
        notulen: "Notulen tidak valid.",
        diskusi: "Topik diskusi tidak ditemukan.",
        dokumen_usulan: "Data usulan tidak valid.",
        arsiprapat: "Arsip tidak ditemukan.",
        dokumentasi: "Dokumentasi tidak valid.",
        penyerahandok: "Data penyerahan tidak valid.",
        laporan: "Data laporan tidak ditemukan.",
    };
    return map[obj] || "Data tidak valid atau tidak ditemukan.";
}

function getFKBlockedText(obj) {
    const map = {
        jadwalrapat:
            "Jadwal tidak dapat dihapus karena sudah digunakan di arsip.",
        user: "User tidak dapat dihapus karena terkait data kehadiran atau usulan.",
        dok_rapat: "Dokumen tidak dapat dihapus karena telah diarsipkan.",
        notulen: "Notulen tidak dapat dihapus karena sudah menjadi arsip.",
        penyerahandok: "Dokumen ini telah digunakan di laporan.",
    };
    return (
        map[obj] || "Data tidak dapat dihapus karena digunakan di modul lain."
    );
}

function getUnauthorizedText(obj) {
    const map = {
        user: "Anda tidak memiliki hak akses untuk mengelola user.",
        jadwalrapat: "Anda tidak diizinkan mengakses jadwal rapat.",
        laporan: "Anda tidak memiliki izin untuk melihat laporan ini.",
        dokumen_usulan: "Akses hanya diberikan kepada anggota rapat.",
        penyerahandok: "Hanya admin yang dapat mengelola penyerahan dokumen.",
    };
    return map[obj] || "Anda tidak memiliki izin untuk mengakses halaman ini.";
}

function getNochangeText(obj) {
    const map = {
        user: "Tidak ada perubahan pada data user.",
        jadwalrapat: "Jadwal rapat tidak mengalami perubahan.",
        dok_rapat: "Data dokumen tidak berubah.",
        notulen: "Isi notulen tidak berubah.",
        dokumen_usulan: "Tidak ada perubahan pada dokumen usulan.",
        penyerahandok: "Tidak ada data yang diperbarui.",
    };
    return map[obj] || "Tidak ada perubahan yang dilakukan.";
}

// 🔁 Konfirmasi Hapus
function confirmDelete(url) {
    Swal.fire({
        title: "Yakin ingin menghapus?",
        text: "Data akan dihapus secara permanen.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
