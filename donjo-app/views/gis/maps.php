<?php
/**
 * File ini:
 *
 * View untuk modul Pemetaan di Halaman Admin
 *
 * /donjo-app/views/gis/maps.php
 *
 */

/**
 *
 * File ini bagian dari:
 *
 * OpenSID
 *
 * Sistem informasi desa sumber terbuka untuk memajukan desa
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * Hak Cipta 2016 - 2020 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:

 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.

 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package OpenSID
 * @author  Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * @copyright Hak Cipta 2016 - 2020 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license http://www.gnu.org/licenses/gpl.html  GPL V3
 * @link  https://github.com/OpenSID/OpenSID
 */
?>

<script>
(function()
{
  var infoWindow;
  window.onload = function()
  {
		<?php if (!empty($desa['lat']) AND !empty($desa['lng'])): ?>
			var posisi = [<?=$desa['lat'].",".$desa['lng']?>];
			var zoom = <?=$desa['zoom'] ?: 10?>;
		<?php elseif (!empty($desa['path'])): ?>
			var wilayah_desa = <?=$desa['path']?>;
			var posisi = wilayah_desa[0][0];
			var zoom = <?=$desa['zoom'] ?: 10?>;
		<?php else: ?>
			var posisi = [-1.0546279422758742,116.71875000000001];
			var zoom   = 10;
		<?php endif; ?>

		//Inisialisasi tampilan peta
    var mymap = L.map('map').setView(posisi, zoom);

    //1. Menampilkan overlayLayers Peta Semua Wilayah
    var marker_desa = [];
    var marker_dusun = [];
    var marker_rw = [];
    var marker_rt = [];
    var semua_marker = [];
    var markers = new L.MarkerClusterGroup();
    var markersList = [];

    //OVERLAY WILAYAH DESA
    <?php if (!empty($desa['path'])): ?>
      set_marker_desa_content(marker_desa, <?=json_encode($desa)?>, "<?=ucwords($this->setting->sebutan_desa).' '.$desa['nama_desa']?>", "<?= favico_desa()?>", '#isi_popup');
    <?php endif; ?>

    //OVERLAY WILAYAH DUSUN
    <?php if (!empty($dusun_gis)): ?>
      set_marker_content(marker_dusun, '<?=addslashes(json_encode($dusun_gis))?>', '#FFFF00', '<?=ucwords($this->setting->sebutan_dusun)?>', 'dusun', '#isi_popup_dusun_');
    <?php endif; ?>

    //OVERLAY WILAYAH RW
    <?php if (!empty($rw_gis)): ?>
      set_marker_content(marker_rw, '<?=addslashes(json_encode($rw_gis))?>', '#8888dd', 'RW', 'rw', '#isi_popup_rw_');
    <?php endif; ?>

    //OVERLAY WILAYAH RT
    <?php if (!empty($rt_gis)): ?>
      set_marker_content(marker_rt, '<?=addslashes(json_encode($rt_gis))?>', '#008000', 'RT', 'rt', '#isi_popup_rt_');
    <?php endif; ?>

    //Menampilkan overlayLayers Peta Semua Wilayah
    var overlayLayers = overlayWil(marker_desa, marker_dusun, marker_rw, marker_rt);

    //Menampilkan BaseLayers Peta
    var baseLayers = getBaseLayers(mymap, '<?=$this->setting->google_key?>');

    L.control.layers(baseLayers, overlayLayers, {position: 'topleft', collapsed: true}).addTo(mymap);

		$('#isi_popup').remove();
		$('#isi_popup_dusun').remove();
		$('#isi_popup_rw').remove();
		$('#isi_popup_rt').remove();

		//LOKASI DAN PROPERTI
		<?php if ($layer_lokasi == 1 AND !empty($lokasi)): ?>
			var daftar_lokasi = JSON.parse('<?=addslashes(json_encode($lokasi))?>');
			var jml = daftar_lokasi.length;
			var content;
			var foto;
			var path_foto = '<?= base_url()."assets/images/gis/point/"?>';
			var point_style = {
			  iconSize: [32, 37],
			  iconAnchor: [16, 37],
			  popupAnchor: [0, -28],
			};

			for (var x = 0; x < jml; x++)
			{
			  if (daftar_lokasi[x].lat)
			  {
					point_style.iconUrl = path_foto+daftar_lokasi[x].simbol;
					if (daftar_lokasi[x].foto)
					{
					  foto = '<td><img src="'+AmbilFotoLokasi(daftar_lokasi[x].foto)+'" class="foto_pend"/></td>';
					}
					else
						foto = '';
					content = '<div id="content">'+
					'<div id="siteNotice">'+
					'</div>'+
					'<h4 id="firstHeading" class="firstHeading">'+daftar_lokasi[x].nama+'</h4>'+
					'<div id="bodyContent">'+ foto +
					'<p>'+daftar_lokasi[x].desk+'</p>'+
					'</div>'+
					'</div>';
					semua_marker.push(turf.point([daftar_lokasi[x].lng, daftar_lokasi[x].lat], {content: content,style: L.icon(point_style)}));
			  }
			}
		<?php endif; ?>

		//AREA
		<?php if ($layer_area==1 AND !empty($area)): ?>
			var daftar_area = JSON.parse('<?=addslashes(json_encode($area))?>');
			var jml = daftar_area.length;
			var jml_path;
			var foto;
			var content_area;
			var lokasi_gambar = "<?= base_url().LOKASI_FOTO_AREA?>";

			for (var x = 0; x < jml;x++)
			{
        if (daftar_area[x].path)
			  {
          daftar_area[x].path = JSON.parse(daftar_area[x].path)
  				jml_path = daftar_area[x].path[0].length;
  				for (var y = 0; y < jml_path; y++)
  				{
  				  daftar_area[x].path[0][y].reverse()
  				}
  				if (daftar_area[x].foto)
  				{
  				  foto = '<img src="'+lokasi_gambar+'sedang_'+daftar_area[x].foto+'" style=" width:200px;height:140px;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px;border:2px solid #555555;"/>';
  				}
  				else
	  				foto = "";

          //Style polygon
    			var area_style = {
    			  stroke: true,
    			  opacity: 1,
    			  weight: 2,
            fillColor: daftar_area[x].color,
    			  fillOpacity: 0.5
    			}
  				content_area =
  				'<div id="content">'+
  				'<div id="siteNotice">'+
  				'</div>'+
  				'<h4 id="firstHeading" class="firstHeading">'+daftar_area[x].nama+'</h4>'+
  				'<div id="bodyContent">'+ foto +
  				'<p>'+daftar_area[x].desk+'</p>'+
  				'</div>'+
  				'</div>';

          daftar_area[x].path[0].push(daftar_area[x].path[0][0])
  				//Menambahkan point ke marker
          semua_marker.push(turf.polygon(daftar_area[x].path, {content: content_area, style: area_style}));
			  }
			}
		<?php endif; ?>

    //GARIS
    <?php if ($layer_garis==1 AND !empty($garis)): ?>
	    var daftar_garis = JSON.parse('<?=addslashes(json_encode($garis))?>');
	    var jml = daftar_garis.length;
	    var coords;
	    var lengthOfCoords;
	    var foto;
	    var content_garis;
	    var lokasi_gambar = "<?= base_url().LOKASI_FOTO_GARIS?>";

	    for (var x = 0; x < jml;x++)
	    {
	      if (daftar_garis[x].path)
	      {
	        daftar_garis[x].path = JSON.parse(daftar_garis[x].path)
	        coords = daftar_garis[x].path;
	        lengthOfCoords = coords.length;

	        for (i = 0; i < lengthOfCoords; i++)
	        {
	          holdLon = coords[i][0];
	          coords[i][0] = coords[i][1];
	          coords[i][1] = holdLon;
	        }

	        if (daftar_garis[x].foto)
	        {
	          foto = '<img src="'+lokasi_gambar+'sedang_'+daftar_garis[x].foto+'" style=" width:200px;height:140px;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px;border:2px solid #555555;"/>';
	        }
	        else
		        foto = "";
	        //Style polyline
	        var garis_style = {
	          stroke: true,
	          opacity: 1,
	          weight: 3,
	          color: daftar_garis[x].color
	        }
	        content_garis =
	        '<div id="content">'+
	        '<div id="siteNotice">'+
	        '</div>'+
	        '<h4 id="firstHeading" class="firstHeading">'+daftar_garis[x].nama+'</h4>'+
	        '<div id="bodyContent">'+ foto +
	        '</div>'+
	        '</div>';

	        semua_marker.push(turf.lineString(coords, {content: content_garis, style: garis_style}));
	      }
	    }
    <?php endif; ?>

		//PENDUDUK
		<?php if ($layer_penduduk==1 OR $layer_keluarga==1 AND !empty($penduduk)): ?>
			//Data penduduk
			var penduduk = JSON.parse('<?=addslashes(json_encode($penduduk))?>');
			var jml = penduduk.length;
			var foto;
			var content;
			var point_style = L.icon({
			  iconUrl: '<?= base_url()."assets/images/gis/point/pend.png"?>',
			  iconSize: [22, 27],
			  iconAnchor: [11, 27],
			  popupAnchor: [0, -28],
			});
			for (var x = 0; x < jml; x++)
			{
			  if (penduduk[x].lat || penduduk[x].lng)
			  {
					//Jika penduduk ada foto, maka pakai foto tersebut
					//Jika tidak, pakai foto default
					if (penduduk[x].foto)
					{
					  foto = '<td><img src="'+AmbilFoto(penduduk[x].foto)+'" class="foto_pend"/></td>';
					}
					else
						foto = '<td><img src="<?= base_url()?>assets/files/user_pict/kuser.png" class="foto_pend"/></td>';

					//Konten yang akan ditampilkan saat marker diklik
					content =
					'<table border=0><tr>' + foto +
					'<td style="padding-left:2px"><font size="2.5" style="bold">'+penduduk[x].nama+'</font> - '+penduduk[x].sex+
					'<p>'+penduduk[x].umur+' Tahun '+penduduk[x].agama+'</p>'+
					'<p>'+penduduk[x].alamat+'</p>'+
					'<p><a href="<?=site_url("penduduk/detail/1/0/")?>'+penduduk[x].id+'" target="ajax-modalx" rel="content" header="Rincian Data '+penduduk[x].nama+'" >Data Rincian</a></p></td>'+
					'</tr></table>';
					//Menambahkan point ke marker
					semua_marker.push(turf.point([Number(penduduk[x].lng), Number(penduduk[x].lat)], {content: content, style: point_style}));
			  }
			}
		<?php endif; ?>

		//Jika tidak ada centang yang dipilih, maka tidak perlu memproses geojson
		if (semua_marker.length != 0)
		{
		  //Menjalankan geojson menggunakan leaflet
		  var geojson = L.geoJSON(turf.featureCollection(semua_marker), {
        pmIgnore: true,
    		showMeasurements: true,
			//Method yang dijalankan ketika marker diklik
			onEachFeature: function (feature, layer) {
			  //Menampilkan pesan berisi content pada saat diklik
			  layer.bindPopup(feature.properties.content);
			  layer.bindTooltip(feature.properties.content);
			},
			//Method untuk menambahkan style ke polygon dan line
			style: function(feature)
			{
			  if (feature.properties.style)
			  {
					return feature.properties.style;
			  }
			},
			//Method untuk menambahkan style ke point (titik marker)
      pointToLayer: function (feature, latlng)
			{
			  if (feature.properties.style)
			  {
					return L.marker(latlng, {icon: feature.properties.style});
			  }
			  else
				  return L.marker(latlng);
			}
    });

      markersList.push(geojson);
  		markers.addLayer(geojson);
      mymap.addLayer(markers);

      //Mempusatkan tampilan map agar semua marker terlihat
		  mymap.fitBounds(geojson.getBounds());
		}
  }; //EOF window.onload

})();
</script>
<style>
#map
{
  width:100%;
  height:86vh
}
.form-group a
{
  color: #FEFFFF;
}
.foto
{
  width:200px;
  height:140px;
  border-radius:3px;
  -moz-border-radius:3px;
  -webkit-border-radius:3px;
  border:2px solid #555555;
}
.icos
{
  padding-top: 9px;
}
.foto_pend
{
  width:70px;height:70px;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px;
}
.leaflet-control-layers {
	display: block;
	position: relative;
}

</style>
<div class="content-wrapper">
	<form id="mainform_map" name="mainform_map" action="" method="post">
		<div class="row">
			<div class="col-md-12">
				<div id="map">
					<div class="leaflet-top leaflet-right">
						<div class="leaflet-control-layers leaflet-bar leaflet-control">
							<a class="leaflet-control-control icos" href="#" title="Control Panel" role="button" aria-label="Control Panel" onclick="$('#target1').toggle();$('#target1').removeClass('hidden');$('#target2').hide();"><i class="fa fa-gears"></i></a>
							<a class="leaflet-control-control icos" href="#" title="Legenda" role="button" aria-label="Legenda" onclick="$('#target2').toggle();$('#target2').removeClass('hidden');$('#target1').hide();"><i class="fa fa-list"></i></a>
						</div>
						<?php $this->load->view("gis/content_desa.php", array('desa' => $desa, 'list_lap' => $list_lap, 'wilayah' => ucwords($this->setting->sebutan_desa.' '.$desa['nama_desa']))) ?>
						<?php $this->load->view("gis/content_dusun.php", array('dusun_gis' => $dusun_gis, 'list_lap' => $list_lap, 'wilayah' => ucwords($this->setting->sebutan_dusun.' '))) ?>
						<?php $this->load->view("gis/content_rw.php", array('rw_gis' => $rw_gis, 'list_lap' => $list_lap, 'wilayah' => ucwords($this->setting->sebutan_dusun.' '))) ?>
						<?php $this->load->view("gis/content_rt.php", array('rt_gis' => $rt_gis, 'list_lap' => $list_lap, 'wilayah' => ucwords($this->setting->sebutan_dusun.' '))) ?>
						<div id="target1" class="leaflet-control-layers leaflet-control-layers-expanded leaflet-control hidden" aria-haspopup="true" style="max-width: 250px;">
							<div class="leaflet-control-layers-overlays">
								<div class="leaflet-control-layers-group" id="leaflet-control-layers-group-2">
									<span class="leaflet-control-layers-group-name">CARI PENDUDUK</span>
									<div class="leaflet-control-layers-separator"></div>
										<div class="form-group">
											<label>Status Penduduk</label>
											<select class="form-control input-sm " name="filter" onchange="formAction('mainform_map','<?= site_url('gis/filter')?>')">
												<option value=""> -- </option>
												<?php foreach ($list_status_penduduk AS $data): ?>
													<option value="<?= $data['id']?>" <?php selected($filter, $data['id']); ?>><?= $data['nama']?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Jenis Kelamin</label>
											<select class="form-control input-sm " name="sex" onchange="formAction('mainform_map','<?= site_url('gis/sex')?>')">
												<option value=""> -- </option>
												<?php foreach ($list_jenis_kelamin AS $data): ?>
													<option value="<?= $data['id']?>" <?php selected($sex, $data['id']); ?>><?= $data['nama']?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
										<label><?=ucwords($this->setting->sebutan_dusun)?></label>
											<select class="form-control input-sm " name="dusun" onchange="formAction('mainform_map','<?= site_url('gis/dusun')?>')">
												<option value=""> -- </option>
												<?php foreach ($list_dusun as $data): ?>
													<option value="<?=$data['dusun']?>" <?php selected($dusun, $data['dusun']); ?>><?=$data['dusun']?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<?php if ($dusun): ?>
											<div class="form-group">
												<label>RW</label>
												<select class="form-control input-sm " name="rw" onchange="formAction('mainform_map','<?= site_url('gis/rw')?>')">
													<option value=""> -- </option>
													<?php foreach ($list_rw as $data): ?>
														<option value="<?=$data['rw']?>" <?php selected($rw, $data['rw']); ?>><?=$data['rw']?></option>
													<?php endforeach; ?>
												</select>
											</div>
											<?php if ($rw): ?>
												<div class="form-group">
													<label>RT</label>
													<select class="form-control input-sm " name="rt" onchange="formAction('mainform_map','<?= site_url('gis/rt')?>')">
														<option value=""> -- </option>
														<?php foreach ($list_rt as $data): ?>
															<option value="<?=$data['rt']?>" <?php selected($rt, $data['rt']); ?>><?=$data['rt']?></option>
														<?php endforeach; ?>
													</select>
												</div>
											<?php endif; ?>
										<?php endif; ?>
										<div class="col-sm-12">
											<div class="form-group row">
												<label>Cari</label>
												<div class="box-tools">
													<div class="input-group input-group-sm pull-right">
														<input name="cari" id="cari" class="form-control" placeholder="cari..." type="text" value="<?=html_escape($cari)?>" onkeypress="if (event.keyCode == 13):$('#'+'mainform_map').attr('action', '<?=site_url("gis/search")?>');$('#'+'mainform_map').submit();endif">
														<div class="input-group-btn">
															<button type="submit" class="btn btn-default" onclick="$('#'+'mainform_map').attr('action', '<?=site_url("gis/search")?>');$('#'+'mainform_map').submit();"><i class="fa fa-search"></i></button>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group">
											<a href="<?=site_url("gis/ajax_adv_search")?>" class="btn btn-block btn-social bg-olive btn-sm" data-remote="false" data-toggle="modal" data-target="#modalBox" data-title="Pencarian Spesifik" title="Pencarian Spesifik">
												<i class="fa fa-search"></i> Pencarian Spesifik
											</a>
											<a href="<?=site_url("gis/clear")?>" class="btn btn-block btn-social bg-orange btn-sm">
												<i class="fa fa-refresh"></i> Bersihkan
											</a>
										</div>
									</div>
								</div>
							</div>
							<div id="target2" class="leaflet-control-layers leaflet-control-layers-expanded leaflet-control hidden" aria-haspopup="true" style="max-height: 315px;">
								<div class="leaflet-control-layers-overlays">
									<div class="leaflet-control-layers-group" id="leaflet-control-layers-group-3">
										<span class="leaflet-control-layers-group-name">LEGENDA</span>
										<div class="leaflet-control-layers-separator"></div>
										<label>
											<input class="leaflet-control-layers-selector" type="checkbox" name="layer_penduduk" value="1" onchange="handle_pend(this);" <?php if ($layer_penduduk==1): ?>checked<?php endif; ?>>
											<span> Penduduk	</span>
										</label>
										<label>
											<input class="leaflet-control-layers-selector" type="checkbox" name="layer_keluarga" value="1" onchange="handle_kel(this);" <?php if ($layer_keluarga==1): ?>checked<?php endif; ?>>
											<span> Keluarga</span>
										</label>
										<label>
											<input type="checkbox" name="layer_area" value="1"onchange="handle_area(this);" <?php if ($layer_area==1): ?>checked<?php endif; ?>>
											<span> Area</span>
										</label>
										<label>
											<input type="checkbox" name="layer_lokasi" value="1"onchange="handle_lokasi(this);" <?php if ($layer_lokasi==1): ?>checked<?php endif; ?>>
											<span> Lokasi/Properti </span>
										</label>
										<label>
											<input type="checkbox" name="layer_garis" value="1"onchange="handle_garis(this);" <?php if ($layer_garis==1): ?>checked<?php endif; ?>>
											<span> Garis </span>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
	function handle_pend(cb)
	{
	  formAction('mainform_map', '<?=site_url('gis')?>/layer_penduduk');
	}
	function handle_kel(cb)
	{
	  formAction('mainform_map', '<?=site_url('gis')?>/layer_keluarga');
	}
	function handle_area(cb)
	{
	  formAction('mainform_map', '<?=site_url('gis')?>/layer_area');
	}
	function handle_lokasi(cb)
	{
	  formAction('mainform_map', '<?=site_url('gis')?>/layer_lokasi');
	}
  function handle_garis(cb)
	{
	  formAction('mainform_map', '<?=site_url('gis')?>/layer_garis');
	}
	function AmbilFoto(foto, ukuran = "kecil_")
	{
	  ukuran_foto = ukuran || null
	  file_foto = '<?= base_url().LOKASI_USER_PICT;?>'+ukuran_foto+foto;
	  return file_foto;
	}
	function AmbilFotoLokasi(foto, ukuran = "kecil_")
	{
	  ukuran_foto = ukuran || null
	  file_foto = '<?= base_url().LOKASI_FOTO_LOKASI;?>'+ukuran_foto+foto;
	  return file_foto;
	}
</script>
