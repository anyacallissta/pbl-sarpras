@extends('layouts.app')

@section('content')

<!-- Flash Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Laporan Penugasan Perbaikan Fasilitas</h5>
        <div class="position-relative" style="max-width: 300px; width: 100%;">
            <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
            <input 
            type="text" 
            id="searchInput" 
            class="form-control form-control-sm" 
            placeholder="Cari..." 
            style="background-color: #f8f9fa; border: 1px solid #ced4da; color: #495057; font-weight: 400; font-size: 1rem; height: 42px; padding-left: 2.5rem;" />
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead class="table-primary">
                <tr>
                    <th style="font-weight: bold;">No</th>
                    <th style="font-weight: bold;">Tanggal Laporan</th>
                    <th style="font-weight: bold;">Nama Sarana</th>
                    <th style="font-weight: bold;">Lokasi Fasilitas</th>
                    <th style="font-weight: bold;" class="text-center">Status Penugasan</th>
                    <th style="font-weight: bold;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penugasans as $penugasan)
                    <tr>
                        <td>{{ $loop->iteration + ($penugasans->firstItem() - 1) }}</td>
                        <td>{{ \Carbon\Carbon::parse($penugasan->laporan->tanggal_laporan)->format('d-m-Y') }}</td>
                        <td>{{ $penugasan->laporan->kerusakan->item->nama ?? '-' }}</td>
                        <td>{{ $penugasan->laporan->kerusakan->item->ruang
                            ? $penugasan->laporan->kerusakan->item->ruang->nama . ', ' . $penugasan->laporan->kerusakan->item->ruang->gedung->nama
                            : ($penugasan->laporan->kerusakan->item->fasum->nama ?? '-'); }}
                        </td>
                        <td class="text-center">
                            @if ($penugasan)
                                @switch($penugasan->status_penugasan)
                                    @case('Progress')
                                        <span style="color: #007bff; border: 1px solid #007bff; padding: 4px 8px; border-radius: 5px; display: inline-block; width: 100px;">Progress</span>
                                        @break
                                    @case('Revisi')
                                        <span style="color: #dc3545; border: 1px solid #dc3545; padding: 4px 8px; border-radius: 5px; display: inline-block; width: 100px;">Revisi</span>
                                        @break
                                    @case('Menunggu')
                                        <span style="color: #ffc107; border: 1px solid #ffc107; padding: 4px 8px; border-radius: 5px; display: inline-block; width: 100px;">Menunggu</span>
                                        @break
                                    @case('Selesai')
                                        <span style="color: #28a745; border: 1px solid #28a745; padding: 4px 8px; border-radius: 5px; display: inline-block; width: 100px;">Selesai</span>
                                        @break
                                    @default
                                        <span class="d-inline-block" style="width:100px;">-</span>
                                @endswitch
                            @else
                                <span class="d-inline-block" style="width:100px;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary detail-laporan" data-id="{{ $penugasan->laporan->laporan_id }}">Detail</button>
                                @if (is_null($penugasan->status_penugasan))
                                    <button type="button" class="btn btn-sm btn-success" style="width: 80px;" onclick="showKerjakanModal('{{ $penugasan->penugasan_id }}')">Kerjakan</button>
                                @elseif (in_array($penugasan->status_penugasan, ['Progress', 'Revisi']))
                                    <button type="button" class="btn btn-sm btn-warning report-penugasan" data-id="{{ $penugasan->penugasan_id }}" style="width: 80px;">Lapor</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada data laporan penugasan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-end mt-3 me-3">
        @if ($penugasans->hasPages())
            <x-pagination :paginator="$penugasans" />
        @endif
    </div>
</div>

@include('teknisi.show')
@include('teknisi.kerjakan')
@include('teknisi.report')

<script>
    
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            Swal.fire({
                title: "Berhasil",
                text: "{{ session('success') }}",
                icon: "success",
                timer: 3000
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: "Gagal",
                text: "{{ session('error') }}",
                icon: "error"
            });
        @endif
        
        // Handle detail button on click
        $('.detail-laporan').on('click', function () {
            const laporanId = $(this).data('id');
            $.ajax({
                url: "{{ url('teknisi/penugasan') }}/" + laporanId + "/show",
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    const laporan = response.laporan || {};
                    const penugasan = response.penugasan || {};
                    const kerusakan = laporan.kerusakan || {};
                    const feedback = laporan.feedback || {};
                    const teknisi = penugasan.teknisi || {};

                    // Set status_laporan dengan badge warna
                    const status_laporan = laporan.status_laporan ?? '-';
                    function renderStatusLaporanBadge(status_laporan) {
                        const baseStyle = "padding: 4px 8px; border-radius: 5px; display: inline-block; width: 100px; text-align: center; font-weight: bold;";
                        switch (status_laporan) {
                            case 'Diajukan':
                                return `<span style="background-color: #ffe8cc; color: #000; ${baseStyle}">Diajukan</span>`;
                            case 'Disetujui':
                                return `<span style="background-color: #d0ebff; color: #1c7ed6; ${baseStyle}">Disetujui</span>`;
                            case 'Dikerjakan':
                                return `<span style="background-color: #fff3bf; color: #f59f00; ${baseStyle}">Dikerjakan</span>`;
                            case 'Selesai':
                                return `<span style="background-color: #d3f9d8; color: #37b24d; ${baseStyle}">Selesai</span>`;
                            case 'Ditolak':
                                return `<span style="background-color: #ffe3e3; color: #f03e3e; ${baseStyle}">Ditolak</span>`;
                            default:
                                return `<span style="display: inline-block; width: 100px; text-align: center;">-</span>`;
                        }
                    }
                    $('#status_laporan').html(renderStatusLaporanBadge(status_laporan));

                     // Lokasi Fasilitas
                    let lokasi = '-';
                    if (kerusakan.item?.ruang?.nama && kerusakan.item?.ruang?.gedung?.nama) {
                        lokasi = `${kerusakan.item?.ruang.nama}, ${kerusakan.item?.ruang.gedung.nama}`;
                    } else if (kerusakan.item?.fasum?.nama) {
                        lokasi = kerusakan.item?.fasum.nama;
                    }
                    
                    function formatTanggalDMY(tanggal) {
                        if (!tanggal) return '-';
                        const [year, month, day] = tanggal.split('-');
                        return `${day}-${month}-${year}`;
                    }

                    $('#detail_laporan_id').text(laporan.laporan_id);                    
                    $('#detail_tanggal_laporan').text(formatTanggalDMY(laporan.tanggal_laporan));
                    $('#detail_lokasi_fasilitas').text(lokasi);
                    $('#detail_item').text(kerusakan.item?.nama ?? '-');
                    $('#detail_deskripsi_kerusakan').text(kerusakan.deskripsi_kerusakan ?? '-');
                    $('#detail_pelapor').text(kerusakan.pelapor?.nama_lengkap ?? '-');
                    $('#detail_verifikator').text(laporan.verifikator?.nama_lengkap ?? '-');

                    if(laporan.kerusakan.foto_kerusakan){
                        $('#detail_foto_kerusakan').attr('src', '/storage/' + laporan.kerusakan.foto_kerusakan);
                    } else {
                        $('#detail_foto_kerusakan').attr('src', '');
                    }

                    // Set status_penugasan dengan badge warna
                    const status_penugasan = penugasan.status_penugasan ?? '-';

                    function renderStatusPenugasanBadge(status_penugasan) {
                        const baseStyle = "padding: 4px 8px; border-radius: 5px; display: inline-block; width: 100px; text-align: center;";
                        switch (status_penugasan) {
                            case 'Progress':
                                return `<span style="color: #007bff; border: 1px solid #007bff; ${baseStyle}">Progress</span>`;
                            case 'Selesai':
                                return `<span style="color: #28a745; border: 1px solid #28a745; ${baseStyle}">Selesai</span>`;
                            case 'Revisi':
                                return `<span style="color: #dc3545; border: 1px solid #dc3545; ${baseStyle}">Revisi</span>`;
                            case 'Menunggu':
                                return `<span style="color: #ffc107; border: 1px solid #ffc107; ${baseStyle}">Menunggu</span>`;
                            default:
                                return `<span style="display: inline-block; width: 100px; text-align: center;">-</span>`;
                        }
                    }
                    $('#status_penugasan').html(renderStatusPenugasanBadge(status_penugasan));                    

                    $('#detail_tanggal_mulai').text(formatTanggalDMY(penugasan.tanggal_mulai));
                    $('#detail_tanggal_selesai').text(formatTanggalDMY(penugasan.tanggal_selesai));
                    $('#detail_teknisi').text(teknisi?.nama_lengkap ?? '-');
                    $('#detail_catatan_perbaikan').text(penugasan.catatan_perbaikan ?? '-');

                    if(penugasan.bukti_perbaikan){
                        $('#detail_bukti_perbaikan').attr('src', '/storage/' + penugasan.bukti_perbaikan);
                    } else {
                        $('#detail_bukti_perbaikan').attr('src', '');
                    }

                    $('#detail_komentar').text(feedback.komentar ?? '-');
                    $('#detail_rating').html(feedback.rating ? '⭐'.repeat(feedback.rating) + ` (${feedback.rating})` : '-');

                    // tampilkan/hidden bagian perbaikan & feedback
                    if (laporan.status_laporan === 'Dikerjakan') {
                        $('#card_laporan_disetujui').show();
                        $('#card_perbaikan').show();
                        $('#card_feedback').hide();
                    } else {
                        $('#card_laporan_disetujui').show();
                        $('#card_perbaikan').show();
                        $('#card_feedback').show();
                    }

                    // Tampilkan modal
                    $('#detailLaporan').modal('show');
                },
                error: function () {
                    Swal.fire('Error', 'Gagal mengambil detail laporan.', 'error');
                }
            });
        });

        // Handle report button click
        $('.report-penugasan').on('click', function () {
            const penugasanId = $(this).data('id');

            $.ajax({
                url: "{{ url('teknisi/penugasan') }}/" + penugasanId + "/report",
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    const penugasan = response.penugasan;

                    // Set form action
                    $('#form-report').attr('action', "{{ url('teknisi/penugasan') }}/" + penugasanId + "/report");

                    $('#reportPenugasan').modal('show');
                },
                error: function (xhr) {
                    Swal.fire({
                        title: "Error",
                        text: "Gagal mengambil data penugasan",
                        icon: "error"
                    });
                }
            });
        });

        // Preview image sebelum upload
        $('#bukti_perbaikan').on('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-image').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                $('#preview-image').hide();
            }
        });

        // Search input filtering
        $('#searchInput').on('keyup', function () {
            const keyword = $(this).val().toLowerCase().trim();
            
            $('table tbody tr').each(function () {
                // Cek semua kolom di baris ini
                const rowText = $(this).text().toLowerCase();
                
                if (rowText.indexOf(keyword) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>

@endsection
