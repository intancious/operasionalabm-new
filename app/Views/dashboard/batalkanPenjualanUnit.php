<?= $this->extend('dashboard/template'); ?>

<?= $this->section('content'); ?>
<?php
$request = \Config\Services::request();
$userModel = new \App\Models\UserModel();

$sesiBagian = session()->get('usr_bagian');
$bagianModel = new \App\Models\BagianModel();
$bagian = $bagianModel->find($sesiBagian);
$akses = explode(',', $bagian['bagian_akses']);

$rekeningModel = new \App\Models\KoderekeningModel();
$bpModel = new \App\Models\BiayapenjualanModel();
$tagModel = new \App\Models\TagihanPuModel();
$rekening = $rekeningModel->orderBy('rek_id', 'ASC')->findAll();

$tagihan = $tagModel->where(['tp_pu' => $penjualan['pu_id'], 'tp_angsuran' => NULL])->first();

$kprModel = new \App\Models\KprModel();
$kpr = $kprModel->orderBy('kpr_nama', 'ASC')->findAll();
$penjualanunitModel = new \App\Models\PenjualanunitModel();
$pj = $penjualanunitModel->findAll();
foreach ($pj as $row) {
    $allUnits[] = $row['pu_unit'];
}

$biayaLain = $bpModel->where([
    'bp_penjualan' => $penjualan['pu_id'],
    'bp_kembali' => 0
])->orderBy('bp_id', 'DESC')->findAll();
$biayaPembatalan = $bpModel->where([
    'bp_penjualan' => $penjualan['pu_id'],
    'bp_kembali' => 1
])->orderBy('bp_id', 'DESC')->findAll();

$customerModel = new \App\Models\CustomerModel();
$unitModel = new \App\Models\UnitModel();
$customer = $customerModel->find($penjualan['pu_cust']);
$unit = $unitModel->where('unit.unit_id', $penjualan['pu_unit'])
    ->join('types', 'types.type_id = unit.unit_tipe', 'left')
    ->select('unit.*, types.type_nama')->first();

$hargatrx = $penjualan['pu_harga'] ? str_replace(',', '.', $penjualan['pu_harga']) : 0;
$nilaiAccKpr = $penjualan['pu_nilaiAccKpr'] ? str_replace(',', '.', $penjualan['pu_nilaiAccKpr']) : 0;
$totalBayar = $tagModel->getTotalBayar($penjualan['pu_id'], 1);
$totalBayarKPR = $tagModel->getTotalBayar($penjualan['pu_id'], 2);
$sisaPiutang = ($hargatrx - $totalBayar) - $nilaiAccKpr;
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h5 text-uppercase mb-0 text-gray-800">
        &nbsp;
        <a href="javascript: history.go(-1)"><i class="fas fa-arrow-alt-circle-left mr-2"></i></a>
        <?= $title_bar; ?>
    </h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><?= $title_bar; ?></li>
    </ol>
</div>

<div class="card shadow my-4">
    <div class="card-header bg-primary">
        <h6 class="m-0 font-weight-bold text-uppercase text-white">
            BIAYA LAIN-LAIN
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered dt-responsive wrap small" style="width:100%">
                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>BIAYA</th>
                        <th>URAIAN / KETERANGAN</th>
                        <th>NOMINAL</th>
                        <th>DEBET</th>
                        <th>KREDIT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grandTotal = 0;
                    $biayaLainModel = new \App\Models\BiayaLainModel();
                    if ($biayaLain) {
                        foreach ($biayaLain as $row) {
                            $biayalain = $biayaLainModel->find($row['bp_biayalain']);
                            $biayaDebet = $rekeningModel->find($row['bp_debet']);
                            $biayaKredit = $rekeningModel->find($row['bp_kredit']);
                            $grandTotal += $row['bp_nominal'];
                    ?>
                            <tr>
                                <td class="text-uppercase"><?= date('d-m-Y', strtotime($row['created_at'])); ?></td>
                                <td class="text-uppercase"><?= $biayalain['bl_nama']; ?></td>
                                <td class="text-uppercase"><?= $row['bp_uraian']; ?></td>
                                <td align="right"><?= number_format($row['bp_nominal'], 0, ".", "."); ?></td>
                                <td class="text-uppercase"><?= $row['bp_debet'] ? '(' . $biayaDebet['rek_kode'] . ') ' . $biayaDebet['rek_nama'] : '-'; ?></td>
                                <td class="text-uppercase"><?= $row['bp_kredit'] ? '(' . $biayaKredit['rek_kode'] . ') ' . $biayaKredit['rek_nama'] : '-'; ?></td>
                                <!-- <td>
                                    <form class="d-inline" action="/dashboard/deleteItemBiayaLain" method="post">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="id" value="<?= $row['bp_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin melanjutkan?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td> -->
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center font-italic">Data belum tersedia.</td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="font-weight-bold" colspan="3">TOTAL</td>
                        <td class="font-weight-bold" align="right"><?= number_format($grandTotal, 0, ".", "."); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card shadow my-4">
    <div class="card-header bg-primary">
        <h6 class="m-0 font-weight-bold text-uppercase text-white">
            RIWAYAT PEMBAYARAN
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered dt-responsive wrap small" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NO. FAKTUR</th>
                        <th>TRANSAKSI</th>
                        <th>ANGSURAN</th>
                        <th>TAGIHAN</th>
                        <th>JTH. TEMPO</th>
                        <th>BAYAR</th>
                        <th>TGL. BAYAR</th>
                        <th>KETERANGAN</th>
                        <th>DEBET</th>
                        <th>KREDIT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalNilai = 0;
                    $totalBayar = 0;
                    $riwayat = $tagModel->where('tp_pu', $penjualan['pu_id'])->orderBy('tp_jthtempo', 'ASC')->findAll();
                    if ($riwayat) {
                        $i = 1;
                        foreach ($riwayat as $item) {
                            $nilai = $item['tp_nilai'] ? str_replace(',', '.', $item['tp_nilai']) : 0;
                            $bayar = $item['tp_nominal'] ? str_replace(',', '.', $item['tp_nominal']) : 0;
                            $totalNilai += $nilai;
                            $totalBayar += $bayar;
                            $rekBayarDebet = $rekeningModel->find($item['tp_debet']);
                            $rekBayarKredit = $rekeningModel->find($item['tp_kredit']);
                    ?>
                            <tr <?= $bayar <= 0 ? ' class="text-danger"' : ''; ?>>
                                <td class="text-uppercase"><?= $i++;; ?></td>
                                <td class="text-uppercase"><?= $item['tp_nomor'] ? $item['tp_nomor'] : '-'; ?></td>
                                <td class="text-uppercase"><?= $item['tp_jenis'] == 1 ? 'BAYAR CUSTOMER' : ($item['tp_jenis'] == 2 ? 'BAYAR KPR' : ''); ?></td>
                                <td class="text-uppercase"><?= $item['tp_angsuran'] ? $item['tp_angsuran'] : '-'; ?></td>
                                <td class="text-uppercase"><?= number_format($nilai, 0, ".", "."); ?></td>
                                <td class="text-uppercase"><?= $item['tp_jthtempo'] ? date('d/m/Y', strtotime($item['tp_jthtempo'])) : '-'; ?></td>
                                <td align="right"><?= number_format($bayar, 0, ".", "."); ?></td>
                                <td class="text-uppercase"><?= $item['tp_tglbayar'] ? date('d/m/Y', strtotime($item['tp_tglbayar'])) : '-'; ?></td>
                                <td class="text-uppercase"><?= $item['tp_keterangan'] ? $item['tp_keterangan'] : '-'; ?></td>
                                <td class="text-uppercase"><?= $item['tp_debet'] ? '(' . $rekBayarDebet['rek_kode'] . ') ' . $rekBayarDebet['rek_nama'] : '-'; ?></td>
                                <td class="text-uppercase"><?= $item['tp_kredit'] ? '(' . $rekBayarKredit['rek_kode'] . ') ' . $rekBayarKredit['rek_nama'] : '-'; ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="11" class="text-center font-italic">Data belum tersedia.</td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="font-weight-bold" colspan="4">GRAND TOTAL</td>
                        <td class="font-weight-bold" align="right"><?= number_format($totalNilai, 0, ".", "."); ?></td>
                        <td class="font-weight-bold" align="right"></td>
                        <td class="font-weight-bold" align="right"><?= number_format($totalBayar, 0, ".", "."); ?></td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


<div class="card shadow my-4">
    <div class="card-header bg-primary">
        <h6 class="m-0 font-weight-bold text-uppercase text-white">
            DATA PENJUALAN UNIT
        </h6>
    </div>
    <div class="card-body">
        <div class="row my-3 small">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="jenisSelect"><b>JENIS PENJUALAN</b></label>
                    <select disabled name="jenis" id="jenisSelect" class="form-control selectpicker" data-live-search="true">
                        <option value="" data-tokens="">:: PILIH ::</option>
                        <option value="cash" data-tokens="cash" <?= $penjualan['pu_jenis'] == "cash" ? 'selected' : ''; ?>>CASH</option>
                        <option value="kpr" data-tokens="kpr" <?= $penjualan['pu_jenis'] == "kpr" ? 'selected' : ''; ?>>KPR</option>
                        <option value="kredit" data-tokens="kredit" <?= $penjualan['pu_jenis'] == "kredit" ? 'selected' : ''; ?>>IN HOUSE</option>
                    </select>
                    <div class="invalid-feedback">
                        <?= $validation->getError('jenis'); ?>
                    </div>
                </div>

                <input type="hidden" name="puId" id="puId" value="<?= $penjualan['pu_id']; ?>">
                <input type="hidden" name="tagId" id="tagId" value="<?= $tagihan['tp_id']; ?>">
                <table class="table table-hover table-bordered">
                    <tr>
                        <td style="vertical-align: middle;" class="font-weight-bold text-uppercase">No. Order</td>
                        <td>
                            <input disabled required readonly type="text" name="nomor" id="nomor" class="form-control" value="<?= $penjualan['pu_nomor']; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle;" class="font-weight-bold text-uppercase">Tanggal</td>
                        <td>
                            <input disabled type="text" name="tanggal" id="tanggal" class="form-control" value="<?= date('d-m-Y', strtotime($penjualan['created_at'])); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle;" class="font-weight-bold text-uppercase">Pilih Marketing</td>
                        <td>
                            <select disabled name="marketing" id="marketing" class="form-control selectpicker" data-live-search="true">
                                <option value="" data-tokens="">:: PILIH ::</option>
                                <?php
                                $marketingModel = new \App\Models\MarketingModel();
                                $marketing = $marketingModel->orderBy('m_nama', 'ASC')->findAll();
                                foreach ($marketing as $row) {
                                ?>
                                    <option value="<?= $row['m_id']; ?>" data-tokens="<?= $row['m_nama']; ?>" <?= $penjualan['pu_marketing'] == $row['m_id'] ? 'selected' : ''; ?>><?= strtoupper($row['m_nama']); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle;" class="font-weight-bold text-uppercase">Pilih Customer</td>
                        <td>
                            <select disabled name="customer" id="customerselect" class="form-control selectpicker" data-live-search="true">
                                <option value="" data-tokens="">:: PILIH ::</option>
                                <?php
                                foreach ($customers as $row) {
                                ?>
                                    <option value="<?= $row['cust_id']; ?>" data-tokens="<?= $row['cust_nama']; ?>" <?= $penjualan['pu_cust'] == $row['cust_id'] ? 'selected' : ''; ?>><?= strtoupper($row['cust_nama']); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle;" class="font-weight-bold text-uppercase">Nama</td>
                        <td>
                            <span id="namaCustomer" class="text-uppercase"></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle;" class="font-weight-bold text-uppercase">Alamat</td>
                        <td>
                            <span id="alamatCustomer" class="text-uppercase"></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle;" class="font-weight-bold text-uppercase">No. Telp</td>
                        <td>
                            <span id="telpCustomer" class="text-uppercase"></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle;" class="font-weight-bold text-uppercase">Pilih Unit</td>
                        <td>
                            <select disabled name="unit" id="unitselect" class="form-control selectpicker" data-live-search="true">
                                <option value="" data-tokens="">:: PILIH ::</option>
                                <?php
                                foreach ($units as $row) { ?>
                                    <option value="<?= $row['unit_id']; ?>" data-tokens="<?= $row['unit_nama']; ?> (<?= $row['type_nama']; ?>)" <?= $penjualan['pu_unit'] == $row['unit_id'] ? 'selected' : ''; ?>><?= strtoupper($row['unit_nama']); ?> (<?= $row['type_nama']; ?>)</option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <table class="table table-hover table-bordered mt-3">
                    <tr>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="hrgril">HARGA RIIL</label>
                                <input disabled type="text" name="hrgril" id="hrgril" class="form-control" value="<?= number_format($penjualan['pu_hrgriil'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="nnup">NUP</label>
                                <input disabled type="text" name="nup" id="nnup" class="form-control" value="<?= number_format($penjualan['pu_nup'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="mutu">PENINGKATAN MUTU</label>
                                <input disabled type="text" name="mutu" id="mutu" class="form-control" value="<?= number_format($penjualan['pu_mutu'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="tanahLebih">KELEBIHAN TANAH</label>
                                <input disabled type="text" name="tanahLebih" id="tanahLebih" class="form-control" value="<?= number_format($penjualan['pu_tanahlebih'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="form-group">
                                <label for="sbum">SBUM</label>
                                <input disabled type="text" name="sbum" id="sbum" class="form-control" value="<?= number_format($penjualan['pu_sbum'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="table table-hover table-bordered mt-3">
                    <tr>
                        <td colspan="2">
                            <div class=" form-group">
                                <label for="ajbn">AJBN</label>
                                <input disabled type="text" name="ajbn" id="ajbn" class="form-control" value="<?= number_format($penjualan['pu_ajbn'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="pph">PPH</label>
                                <input disabled type="text" name="pph" id="pph" class="form-control" value="<?= number_format($penjualan['pu_pph'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class=" form-group">
                                <label for="bphtb">BPHTB</label>
                                <input disabled type="text" name="bphtb" id="bphtb" class="form-control" value="<?= number_format($penjualan['pu_bphtb'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="realisasi">REALISASI</label>
                                <input disabled type="text" name="realisasi" id="realisasi" class="form-control" value="<?= number_format($penjualan['pu_realisasi'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-hover table-bordered mt-3">
                    <tr>
                        <td colspan="2">
                            <div class=" form-group">
                                <label for="shm">SHM</label>
                                <input disabled type="text" name="shm" id="shm" class="form-control" value="<?= number_format($penjualan['pu_shm'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="kanopi">KANOPI</label>
                                <input disabled type="text" name="kanopi" id="kanopi" class="form-control" value="<?= number_format($penjualan['pu_kanopi'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class=" form-group">
                                <label for="tandon">TANDON</label>
                                <input disabled type="text" name="tandon" id="tandon" class="form-control" value="<?= number_format($penjualan['pu_tandon'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="pompair">POMPA AIR</label>
                                <input disabled type="text" name="pompair" id="pompair" class="form-control" value="<?= number_format($penjualan['pu_pompair'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class=" form-group">
                                <label for="teralis">TERALIS</label>
                                <input disabled type="text" name="teralis" id="teralis" class="form-control" value="<?= number_format($penjualan['pu_teralis'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="tembok">TEMBOK KELILING</label>
                                <input disabled type="text" name="tembok" id="tembok" class="form-control" value="<?= number_format($penjualan['pu_tembok'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class=" form-group">
                                <label for="pondasi">PONDASI</label>
                                <input disabled type="text" name="pondasi" id="pondasi" class="form-control" value="<?= number_format($penjualan['pu_pondasi'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="table table-hover table-bordered mt-3">
                    <tr>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="pijb">PIJB</label>
                                <input disabled type="text" name="pijb" id="pijb" class="form-control" value="<?= number_format($penjualan['pu_pijb'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="ppn">PPN</label>
                                <input disabled type="text" name="ppn" id="ppn" class="form-control" value="<?= number_format($penjualan['pu_ppn'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class=" form-group">
                                <label for="fee">FEE & KOMISI PENJUALAN</label>
                                <input disabled type="text" name="fee" id="fee" class="form-control" value="<?= number_format($penjualan['pu_fee'], 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="realisasi">REALISASI</label>
                                <input disabled type="text" name="realisasi" id="realisasi" class="form-control" value="<?= number_format($penjualan['pu_realisasi'], 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="table table-hover table-bordered mt-3">
                    <tr>
                        <td colspan="4">
                            <div class="form-group">
                                <label for="hrgtrx">HARGA TRANSAKSI</label>
                                <input disabled type="text" name="hrgtrx" id="hrgtrx" class="form-control" value="<?= number_format($hargatrx, 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                </table>

                <div id="showKpr" class="mb-3 mt-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group">
                                        <label for="kpr"><b>KPR</b></label>
                                        <select disabled name="kpr" id="kpr" class="form-control selectpicker" data-live-search="true">
                                            <option value="" data-tokens="">:: PILIH ::</option>
                                            <?php
                                            foreach ($kpr as $row) { ?>
                                                <option value="<?= $row['kpr_id']; ?>" data-tokens="<?= strtoupper($row['kpr_nama']); ?>" <?= $penjualan['pu_kpr'] == $row['kpr_id'] ? 'selected' : ''; ?>><?= strtoupper($row['kpr_nama']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tglPencairan" class="font-weight-bold text-uppercase">TGL. ACC KPR</label>
                                        <input disabled type="text" name="tglPencairan" id="tglPencairan" class="form-control" value="<?= $penjualan['pu_tglAccKpr'] ? date('d-m-Y', strtotime($penjualan['pu_tglAccKpr'])) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tglPencairan" class="font-weight-bold text-uppercase">TGL. REALISASI KPR</label>
                                        <input disabled type="text" name="tglPencairan" id="tglPencairan" class="form-control" value="<?= $penjualan['pu_tglRealisasiKpr'] ? date('d-m-Y', strtotime($penjualan['pu_tglRealisasiKpr'])) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group">
                                        <label for="nilaiPencairan">NILAI ACC KPR</label>
                                        <input disabled type="text" name="nilaiPencairan" id="nilaiPencairan" class="form-control" value="<?= number_format($nilaiAccKpr, 0, ".", "."); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="totalPencairanKpr">TOTAL PENCAIRAN KPR</label>
                                        <input disabled type="text" name="totalPencairanKpr" id="totalPencairanKpr" class="form-control" value="<?= number_format($totalBayarKPR, 0, ".", "."); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sisaKpr">SISA KPR</label>
                                        <input disabled type="text" name="sisaKpr" id="sisaKpr" class="form-control" value="<?= number_format($nilaiAccKpr - $totalBayarKPR, 0, ".", "."); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-hover table-bordered mt-4">
                    <tr>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="totalBayar">TOTAL BAYAR CUSTOMER</label>
                                <input disabled type="text" name="totalBayar" id="totalBayar" class="form-control" value="<?= number_format($totalBayar, 0, ".", "."); ?>">
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="form-group">
                                <label for="sisa">SISA CUSTOMER</label>
                                <input disabled type="text" name="sisa" id="sisa" class="form-control" value="<?= number_format($sisaPiutang, 0, ".", "."); ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="form-group">
                                <label for="kaliangsur">BERAPA KALI ANGSURAN?</label>
                                <input disabled type="number" name="kaliangsur" id="kaliangsur" class="form-control" value="<?= $penjualan['pu_kaliangsur']; ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="form-group font-weight-bold">
                                <label for="catatan">KETERANGAN</label>
                                <textarea disabled name="catatan" id="catatan" class="form-control" rows="2"><?= $penjualan['pu_keterangan']; ?></textarea>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mt-4">
    <div class="card-header bg-primary">
        <h6 class="m-0 font-weight-bold text-uppercase text-white">
            PENGEMBALIAN DANA
        </h6>
    </div>
    <div class="card-body">
        <div class="row mt-3 small">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tanggalBayarPicker"><b>TGL. PEMBATALAN</b></label>
                    <input required type="text" name="tanggalBayar" id="tanggalBayarPicker" class="form-control datepicker" value="<?= date('d-m-Y'); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="biayalain"><b>BIAYA LAIN</b></label>
                    <select required name="biayalain" id="biayalain" class="form-control selectpicker" data-live-search="true">
                        <option value="" data-tokens="">:: PILIH ::</option>
                        <?php
                        $biayaLainModel = new \App\Models\BiayaLainModel();
                        $biayalain = $biayaLainModel->orderBy('bl_nama', 'ASC')->findAll();
                        foreach ($biayalain as $row) {
                        ?>
                            <option value="<?= $row['bl_id']; ?>" data-tokens="<?= $row['bl_nama']; ?>" <?= old('biayalain') == $row['bl_id'] ? 'selected' : ''; ?>><?= strtoupper($row['bl_nama']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row mt-3 small">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nominal_lain"><b>NOMINAL</b></label>
                    <input type="text" name="nominal_lain" id="nominal_lain" class="form-control">
                </div>
                <div class="form-group">
                    <label for="uraian_lain"><b>URAIAN / KETERANGAN</b></label>
                    <input type="text" name="uraian_lain" id="uraian_lain" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="debet_lain"><b>DEBET</b></label>
                    <select name="debet_lain" id="debet_lain" class="form-control selectpicker" data-live-search="true">
                        <option value="" data-tokens="">:: PILIH ::</option>
                        <?php
                        foreach ($rekening as $row) { ?>
                            <option value="<?= $row['rek_id']; ?>" data-tokens="(<?= $row['rek_kode']; ?>) <?= strtoupper($row['rek_nama']); ?>" <?= old('debet_lain') == $row['rek_id'] ? 'selected' : ''; ?>>(<?= $row['rek_kode']; ?>) <?= strtoupper($row['rek_nama']); ?></option>
                        <?php } ?>
                    </select>
                    <div class="invalid-feedback">
                        <?= $validation->getError('debet_lain'); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="kredit_lain"><b>KREDIT</b></label>
                    <select name="kredit_lain" id="kredit_lain" class="form-control selectpicker" data-live-search="true">
                        <option value="" data-tokens="">:: PILIH ::</option>
                        <?php
                        foreach ($rekening as $row) { ?>
                            <option value="<?= $row['rek_id']; ?>" data-tokens="(<?= $row['rek_kode']; ?>) <?= strtoupper($row['rek_nama']); ?>" <?= old('kredit_lain') == $row['rek_id'] ? 'selected' : ''; ?>>(<?= $row['rek_kode']; ?>) <?= strtoupper($row['rek_nama']); ?></option>
                        <?php } ?>
                    </select>
                    <div class="invalid-feedback">
                        <?= $validation->getError('kredit_lain'); ?>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-primary btn-sm addBiayaPembatalan">TAMBAH</button>
    </div>
</div>

<div class="card shadow my-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered dt-responsive wrap small" style="width:100%">
                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>BIAYA</th>
                        <th>URAIAN / KETERANGAN</th>
                        <th>NOMINAL</th>
                        <th>DEBET</th>
                        <th>KREDIT</th>
                        <th>HAPUS</th>
                    </tr>
                </thead>
                <tbody id="biayaLainList">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mb-5 mt-3">
    <div class="col-md">
        <button type="submit" class="btn btn-primary btn-sm float-right" onclick="pembatalanPenjualanUnit()">SIMPAN</button>
    </div>
</div>

<div class="card shadow my-4">
    <div class="card-header bg-primary">
        <h6 class="m-0 font-weight-bold text-uppercase text-white">
            RIWAYAT PENGEMBALIAN DANA
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered dt-responsive wrap small" style="width:100%">
                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>URAIAN / KETERANGAN</th>
                        <th>NOMINAL</th>
                        <th>DEBET</th>
                        <th>KREDIT</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grandTotal = 0;
                    if ($biayaPembatalan) {
                        foreach ($biayaPembatalan as $row) {
                            $biayaDebet = $rekeningModel->find($row['bp_debet']);
                            $biayaKredit = $rekeningModel->find($row['bp_kredit']);
                            $grandTotal += $row['bp_nominal'];
                    ?>
                            <tr>
                                <td class="text-uppercase"><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                <td class="text-uppercase"><?= $row['bp_uraian']; ?></td>
                                <td align="right"><?= number_format($row['bp_nominal'], 0, ".", "."); ?></td>
                                <td class="text-uppercase"><?= $row['bp_debet'] ? '(' . $biayaDebet['rek_kode'] . ') ' . $biayaDebet['rek_nama'] : '-'; ?></td>
                                <td class="text-uppercase"><?= $row['bp_kredit'] ? '(' . $biayaKredit['rek_kode'] . ') ' . $biayaKredit['rek_nama'] : '-'; ?></td>
                                <td>
                                    <form class="d-inline" action="/dashboard/deleteItemBiayaLain" method="post">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="id" value="<?= $row['bp_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin melanjutkan?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center font-italic">Data belum tersedia.</td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="font-weight-bold" colspan="2">TOTAL</td>
                        <td class="font-weight-bold" align="right"><?= number_format($grandTotal, 0, ".", "."); ?></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>