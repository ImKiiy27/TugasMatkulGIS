const fmt = n => n.toLocaleString('id-ID');
let dataKec = [], dataByNama = {}, mode = 'jumlah', legend;

// ---------- Peta dasar ----------
const map = L.map('map');
const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'
});
const satelit = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 18, attribution: 'Tiles &copy; Esri'
});
osm.addTo(map);

// ---------- Layer tematik ----------
let layerBatas;                          
const layerTitik = L.layerGroup();       
const layerLabel = L.layerGroup();       

function warnaJumlah(n) {
    return n > 120000 ? '#800026' :
           n > 100000 ? '#BD0026' :
           n >  80000 ? '#E31A1C' :
           n >  60000 ? '#FC4E2A' :
           n >  40000 ? '#FD8D3C' : '#FEB24C';
}

function warnaLaju(p) {
    return p <  0    ? '#d73027' :
           p <  0.3  ? '#fee08b' :
           p <  0.6  ? '#91cf60' : '#1a9850';
}

function warnaKepadatan(k) {
    return k > 3000 ? '#4a1486' :
           k > 2000 ? '#6a51a3' :
           k > 1500 ? '#807dba' :
           k > 1000 ? '#9e9ac8' :
           k >  500 ? '#bcbddc' : '#dadaeb';
}

function warna(d) {
    if (!d) return '#cccccc';
    if (mode === 'jumlah') return warnaJumlah(d.jumlah);
    if (mode === 'laju') return warnaLaju(d.laju);
    return warnaKepadatan(d.kepadatan);
}

function isiPopup(d) {
    return `<b>Kec. ${d.nama}</b><br>
            Luas Wilayah: <b>${d.luas.toLocaleString('id-ID')}</b> km²<br>
            Jumlah penduduk: <b>${fmt(d.jumlah)}</b> jiwa<br>
            Kepadatan: <b>${d.kepadatan.toLocaleString('id-ID')}</b> jiwa/km²<br>
            Laju pertumbuhan: <b>${d.laju.toLocaleString('id-ID')}%</b> / tahun`;
}

function gayaBatas(feature) {
    return {
        fillColor: warna(dataByNama[feature.properties.nama]),
        weight: 1.2, color: '#ffffff', opacity: 1, fillOpacity: 0.75
    };
}

const info = L.control({ position: 'topleft' });
info.onAdd = function () { this._div = L.DomUtil.create('div', 'info'); this.update(); return this._div; };
info.update = function (d) {
    this._div.innerHTML = d
        ? `<h4>Kec. ${d.nama}</h4>
           Luas: <b>${d.luas.toLocaleString('id-ID')}</b> km²<br>
           Penduduk: <b>${fmt(d.jumlah)}</b> jiwa<br>
           Kepadatan: <b>${d.kepadatan.toLocaleString('id-ID')}</b> / km²<br>
           Laju: <b>${d.laju.toLocaleString('id-ID')}%</b> / tahun`
        : '<h4>Kabupaten Jember</h4>Arahkan kursor ke sebuah kecamatan';
};

function sorot(e) {
    const l = e.target;
    l.setStyle({ weight: 3, color: '#1e3a5f', fillOpacity: 0.9 });
    l.bringToFront();
    info.update(dataByNama[l.feature.properties.nama]);
}
function lepas(e) {
    layerBatas.resetStyle(e.target);
    info.update();
}

function buatLayerBatas(geojson) {
    layerBatas = L.geoJSON(geojson, {
        style: gayaBatas,
        onEachFeature: (feature, layer) => {
            const d = dataByNama[feature.properties.nama];
            if (d) layer.bindPopup(isiPopup(d));
            layer.on({ mouseover: sorot, mouseout: lepas });

            L.tooltip({ permanent: true, direction: 'center', className: 'label-kec' })
                .setLatLng(feature.properties.label)
                .setContent(feature.properties.nama)
                .addTo(layerLabel);
        }
    });
}

function buatLayerTitik() {
    layerTitik.clearLayers();
    dataKec.forEach(d => {
        let val = mode === 'kepadatan' ? d.kepadatan : d.jumlah;
        let r = mode === 'kepadatan' ? Math.sqrt(val)/3 : Math.sqrt(val)/18;
        L.circleMarker([d.lat, d.lng], {
            radius: r,
            fillColor: warna(d), color: '#333', weight: 1, fillOpacity: 0.85
        }).bindPopup(isiPopup(d)).addTo(layerTitik);
    });
}

function buatLegenda() {
    if (legend) map.removeControl(legend);
    legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'legend');
        if (mode === 'jumlah') {
            div.innerHTML = '<b>Jumlah Penduduk (jiwa)</b><br>';
            const batas = [0, 40000, 60000, 80000, 100000, 120000];
            batas.forEach((b, i) => {
                div.innerHTML += `<i style="background:${warnaJumlah(b + 1)}"></i>` +
                    (batas[i + 1] ? `${fmt(b)} – ${fmt(batas[i + 1])}` : `> ${fmt(b)}`) + '<br>';
            });
        } else if (mode === 'laju') {
            div.innerHTML = '<b>Laju Pertumbuhan</b><br>' +
                `<i style="background:${warnaLaju(-1)}"></i> Negatif (&lt; 0%)<br>` +
                `<i style="background:${warnaLaju(0.1)}"></i> 0 – 0,3%<br>` +
                `<i style="background:${warnaLaju(0.4)}"></i> 0,3 – 0,6%<br>` +
                `<i style="background:${warnaLaju(0.8)}"></i> ≥ 0,6%`;
        } else if (mode === 'kepadatan') {
            div.innerHTML = '<b>Kepadatan (jiwa/km²)</b><br>';
            const batas = [0, 500, 1000, 1500, 2000, 3000];
            batas.forEach((b, i) => {
                div.innerHTML += `<i style="background:${warnaKepadatan(b + 1)}"></i>` +
                    (batas[i + 1] ? `${fmt(b)} – ${fmt(batas[i + 1])}` : `> ${fmt(b)}`) + '<br>';
            });
        }
        return div;
    };
    legend.addTo(map);
}

function gantiMode(m) {
    mode = m;
    document.getElementById('btnJumlah').classList.toggle('active', m === 'jumlah');
    document.getElementById('btnLaju').classList.toggle('active', m === 'laju');
    document.getElementById('btnKepadatan').classList.toggle('active', m === 'kepadatan');
    if (layerBatas) layerBatas.setStyle(gayaBatas);
    buatLayerTitik();
    buatLegenda();
}

// ---------- Grafik ----------
function buatGrafik() {
    new Chart(document.getElementById('chartJumlah'), {
        type: 'bar',
        data: {
            labels: dataKec.map(d => d.nama),
            datasets: [{ label: 'Jiwa', data: dataKec.map(d => d.jumlah),
                         backgroundColor: dataKec.map(d => warnaJumlah(d.jumlah)) }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } },
                   scales: { y: { ticks: { autoSkip: false, font: { size: 10 } } } } }
    });

    const urutKepadatan = [...dataKec].sort((a, b) => b.kepadatan - a.kepadatan);
    new Chart(document.getElementById('chartKepadatan'), {
        type: 'bar',
        data: {
            labels: urutKepadatan.map(d => d.nama),
            datasets: [{ label: 'Jiwa/km²', data: urutKepadatan.map(d => d.kepadatan),
                         backgroundColor: urutKepadatan.map(d => warnaKepadatan(d.kepadatan)) }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } },
                   scales: { y: { ticks: { autoSkip: false, font: { size: 10 } } } } }
    });
}

// ---------- Ambil data ----------
Promise.all([
    fetch('api/api.php').then(r => r.json()),
    fetch('data/jember_kecamatan.geojson').then(r => r.json())
]).then(([data, geojson]) => {
    dataKec = data;
    dataKec.forEach(d => dataByNama[d.nama] = d);

    buatLayerBatas(geojson);
    buatLayerTitik();

    layerBatas.addTo(map);
    layerLabel.addTo(map);
    map.fitBounds(layerBatas.getBounds(), { padding: [10, 10] });

    L.control.layers(
        { 'OpenStreetMap': osm, 'Citra Satelit': satelit },
        {
            'Batas Kecamatan (choropleth)': layerBatas,
            'Label Nama Kecamatan': layerLabel,
            'Lingkaran Proporsional': layerTitik
        },
        { collapsed: false, position: 'topright' }
    ).addTo(map);

    info.addTo(map);
    L.control.scale({ imperial: false }).addTo(map);
    buatLegenda();
    buatGrafik();
}).catch(err => alert('Gagal memuat data: ' + err));
