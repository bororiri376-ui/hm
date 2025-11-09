<?php
  $pageTitle = 'Anggota BPH';
  require __DIR__ . '/includes/header.php';

  $anggota = bph_all();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Anggota BPH</h1>
</div>
<?php
  $pos = fn($m) => (string)($m['position'] ?? '');
  $key = fn($m) => (string)(($m['name'] ?? '').($m['position'] ?? ''));
  $top = array_values(array_filter((array)$anggota, function($m){ $p=$m['position']??''; return in_array($p, ['Ketua','Wakil'], true); }));
  $sek = array_values(array_filter((array)$anggota, function($m){ $p=$m['position']??''; return in_array($p, ['Sekretaris 1','Sekretaris 2'], true); }));
  $bend = array_values(array_filter((array)$anggota, function($m){ $p=$m['position']??''; return in_array($p, ['Bendahara 1','Bendahara 2'], true); }));
  $dept_st  = array_values(array_filter((array)$anggota, function($m){ return ($m['position']??'') === 'Departemen Sains Dan Teknologi'; }));
  $dept_hm  = array_values(array_filter((array)$anggota, function($m){ return ($m['position']??'') === 'Departemen Humas'; }));
  $dept_ol  = array_values(array_filter((array)$anggota, function($m){ return ($m['position']??'') === 'Departemen Olahraga'; }));
  $dept_kr  = array_values(array_filter((array)$anggota, function($m){ return ($m['position']??'') === 'Departemen Kerohanian'; }));
  $dept_ki  = array_values(array_filter((array)$anggota, function($m){ return ($m['position']??'') === 'Departemen Kominfo'; }));
  $dept_mm  = array_values(array_filter((array)$anggota, function($m){ return ($m['position']??'') === 'Departemen Multimedia'; }));
  $used = array_merge(
    array_map($key, $top),
    array_map($key, $sek),
    array_map($key, $bend),
    array_map($key, $dept_st),
    array_map($key, $dept_hm),
    array_map($key, $dept_ol),
    array_map($key, $dept_kr),
    array_map($key, $dept_ki),
    array_map($key, $dept_mm)
  );
  $others = array_values(array_filter((array)$anggota, function($m) use ($key,$used){ return !in_array($key($m), $used, true); }));
?>

<?php if (!empty($top)): ?>
  <div class="mb-2 small text-muted">Ketua & Wakil</div>
  <div class="row g-3 mb-4">
    <?php foreach ($top as $m): ?>
      <div class="col-12 col-md-6">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

 

<?php if (!empty($bend)): ?>
  <div class="mb-2 small text-muted">Bendahara</div>
  <div class="row g-3 mb-4">
    <?php foreach ($bend as $m): ?>
      <div class="col-12 col-md-6">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($dept_st)): ?>
  <div class="mb-2 small text-muted">Departemen Sains Dan Teknologi</div>
  <div class="row g-3 mb-4">
    <?php foreach ($dept_st as $m): ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($dept_hm)): ?>
  <div class="mb-2 small text-muted">Departemen Humas</div>
  <div class="row g-3 mb-4">
    <?php foreach ($dept_hm as $m): ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($dept_ol)): ?>
  <div class="mb-2 small text-muted">Departemen Olahraga</div>
  <div class="row g-3 mb-4">
    <?php foreach ($dept_ol as $m): ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($dept_kr)): ?>
  <div class="mb-2 small text-muted">Departemen Kerohanian</div>
  <div class="row g-3 mb-4">
    <?php foreach ($dept_kr as $m): ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($dept_ki)): ?>
  <div class="mb-2 small text-muted">Departemen Kominfo</div>
  <div class="row g-3 mb-4">
    <?php foreach ($dept_ki as $m): ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($dept_mm)): ?>
  <div class="mb-2 small text-muted">Departemen Multimedia</div>
  <div class="row g-3 mb-4">
    <?php foreach ($dept_mm as $m): ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($sek)): ?>
  <div class="mb-2 small text-muted">Sekretaris</div>
  <div class="row g-3 mb-4">
    <?php foreach ($sek as $m): ?>
      <div class="col-12 col-md-6">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($others)): ?>
  <div class="mb-2 small text-muted">Lainnya</div>
  <div class="row g-3">
    <?php foreach ($others as $m): ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
