<?php session_start(); ?>
<!DOCTYPE html>
<html lang="cs">

<head>
<meta charset="UTF-8">
<title>RadarMapa.cz</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>
html,body{height:100%;margin:0;}
#map{height:100%;}

.topbar{
    position:absolute;
    top:10px;
    left:50%;
    transform:translateX(-50%);
    z-index:1000;
    background:white;
    padding:8px 15px;
    border-radius:8px;
    box-shadow:0 3px 10px rgba(0,0,0,0.2);
    font-family:sans-serif;
    display:flex;
    gap:8px;
    align-items:center;
    flex-wrap:wrap;
}

.leaflet-popup-content{max-width:520px;}

@keyframes spin {
0% { transform: rotate(0deg); }
100% { transform: rotate(360deg); }
}
</style>
</head>

<body>

<div class="topbar">

<strong>RadarMapa.cz</strong>

<input id="aiSearch" placeholder="Zeptej se AI..." style="width:180px;">
<button onclick="askAI()">AI</button>

<select id="countryFilter">
<option value="">Stát</option>
<option value="CZ">Cesko</option>
<option value="SK">Slovensko</option>
<option value="AT">Rakousko</option>
</select>

<select id="regionFilter">
<option value="">Kraj</option>
<option value="JHM">Jihomoravský</option>
<option value="MSK">Moravskoslezský</option>
<option value="PHA">Praha</option>
<option value="STC">Stredočeský</option>
</select>

<label><input type="checkbox" id="fKamera" checked>Kamera</label>
<label><input type="checkbox" id="fRadar" checked>Radar</label>
<label><input type="checkbox" id="fSemafor" checked>Semafor</label>
<label><input type="checkbox" id="fUdalost" checked>Událost</label>
<label><input type="checkbox" id="fTraffic" checked>Doprava</label>

<?php if(isset($_SESSION['user_id'])): ?>
<button id="addRadarBtn">Pridat bod</button>
<button id="addLineBtn">Pridat úsek</button>
<?php else: ?>
<a href="login.php">Prihlásit</a>
<?php endif; ?>

</div>
<div id="aiBox" style="
position:absolute;
top:70px;
left:50%;
transform:translateX(-50%);
z-index:1000;
background:white;
padding:10px 15px;
border-radius:10px;
box-shadow:0 3px 10px rgba(0,0,0,0.2);
font-family:sans-serif;
display:none;
min-width:250px;
">

<div id="aiStatus">🤖 Přemýšlím...</div>

<div id="aiLoader" style="
margin-top:5px;
width:20px;
height:20px;
border:3px solid #ccc;
border-top:3px solid #000;
border-radius:50%;
animation:spin 1s linear infinite;
"></div>

<div id="aiResponse" style="margin-top:10px;font-size:14px;"></div>

</div>
<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>

const HERE_API_KEY = "TVUJ_HERE_API_KEY";

const map = L.map('map',{maxZoom:19,minZoom:3}).setView([49.8,15.5],7);

L.tileLayer('https://api.mapy.cz/v1/maptiles/outdoor/256/{z}/{x}/{y}?apikey=tWiyFgTZGiaKtAJTGGJzvJsKVYreCo72eqBpqD0nTGQ',{
attribution:'© Seznam.cz',
maxZoom:19
}).addTo(map);

const markersLayer = L.layerGroup().addTo(map);
const linesLayer = L.layerGroup().addTo(map);

let addMode=false;
let addLineMode=false;
let currentLine=[];
let tempLine=null;


// IKONY
const radarIcon = L.icon({iconUrl:'/icons/radar.png', iconSize:[32,32]});
const semaforIcon = L.icon({iconUrl:'/radar/icons/cervena.png', iconSize:[32,32]});
const cameraIcon = L.icon({iconUrl:'https://static.vecteezy.com/system/resources/previews/016/016/734/original/transparent-cctv-camera-icon-free-png.png', iconSize:[32,32]});
const cameraendIcon = L.icon({iconUrl:'/radar/icons/x.png', iconSize:[32,32]});
const uradarIcon = L.icon({iconUrl:'https://tse2.mm.bing.net/th/id/OIP.i3W35dE-H9r0cjGdHp-zsAHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', iconSize:[32,32]});
const udalostIcon = L.icon({iconUrl:'https://static.vecteezy.com/system/resources/previews/012/042/292/original/warning-sign-icon-transparent-background-png.png', iconSize:[32,32]});
const kolonaIcon = L.icon({iconUrl:'https://www.freeiconspng.com/uploads/car-heavy-traffic-sign-traffic-icon--28.png', iconSize:[32,32]});

// -------- TRAFFIC --------
function getTraffic(){

if(!fTraffic.checked){
trafficLayer.clearLayers();
return;
}

const bounds = map.getBounds();

fetch(`https://data.traffic.hereapi.com/v7/flow?in=bbox:${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()}&locationReferencing=shape&apiKey=${HERE_API_KEY}`)
.then(res=>res.json())
.then(data=>{

trafficLayer.clearLayers();

if(!data.results) return;

data.results.forEach(r=>{

let jam = r.currentFlow?.jamFactor ?? 0;

let color="green";
if(jam>7) color="red";
else if(jam>4) color="orange";

r.location.shape.links.forEach(link=>{

let coords = link.points.map(p=>[p.lat,p.lng]);

L.polyline(coords,{
color:color,
weight:5,
opacity:0.7
}).addTo(trafficLayer);

});

});

});

}

// -------- LOCAL STORAGE --------
function saveFilters(){
    localStorage.setItem("filters", JSON.stringify({
        country: countryFilter.value,
        region: regionFilter.value,
        fKamera: fKamera.checked,
        fRadar: fRadar.checked,
        fSemafor: fSemafor.checked,
        fUdalost: fUdalost.checked
    }));
}

function loadFilters(){
    let f = JSON.parse(localStorage.getItem("filters")||"{}");

    if(f.country) countryFilter.value=f.country;
    if(f.region) regionFilter.value=f.region;

    if(f.fKamera!==undefined) fKamera.checked=f.fKamera;
    if(f.fRadar!==undefined) fRadar.checked=f.fRadar;
    if(f.fSemafor!==undefined) fSemafor.checked=f.fSemafor;
    if(f.fUdalost!==undefined) fUdalost.checked=f.fUdalost;
}

loadFilters();


// -------- LOAD OBJECTS --------
function loadObjects(){

    const bounds = map.getBounds();

    fetch('/api/objects.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({
            minLat:bounds.getSouth(),
            maxLat:bounds.getNorth(),
            minLng:bounds.getWest(),
            maxLng:bounds.getEast(),
            country: countryFilter.value,
            region: regionFilter.value
        })
    })
    .then(res=>res.json())
    .then(data=>{

        markersLayer.clearLayers();
        linesLayer.clearLayers();

        data.forEach(obj=>{

          if(
(obj.type==="Kamera" && !fKamera.checked) ||
(obj.type==="Radar" && !fRadar.checked) ||
(obj.type==="Semafor" && !fSemafor.checked) ||
((obj.type==="udalost" || obj.type==="Kolona") && !fUdalost.checked)
){
    return;
}

            if(obj.type==="Usek" && obj.coordinates){

                let coords = JSON.parse(obj.coordinates);

                let desc = (obj.description ?? '').replace(/\n/g,'<br>');

                let popup = `<b>${obj.name}</b>`;

                if(desc){
                    popup += `<br>${desc}`;
                }

                let line = L.polyline(coords,{
                    color:"red",
                    weight:8
                }).addTo(linesLayer);

                line.bindPopup(popup);

                return;
            }

            let desc = (obj.description ?? '').replace(/\n/g,'<br>');

            let icon = radarIcon;
            if(obj.type==="Kamera") icon=cameraIcon;
            if(obj.type==="Radar") icon=uradarIcon;
            if(obj.type==="Kameraend") icon=cameraendIcon;
            if(obj.type==="Semafor") icon=semaforIcon;
            if(obj.type==="udalost") icon=udalostIcon;
	    if(obj.type==="Kolona") icon=kolonaIcon;


            if(obj.reload_url){

                let frameId = "frame_"+obj.id;

                let popup = `
                <div style="width:550px">
                <b>${obj.name}</b><br>
                ${desc}
                <br><br>
                <iframe id="${frameId}" src="${obj.reload_url}" style="width:509px;height:415px;border:0;border-radius:10px;"></iframe>
                </div>`;

                let marker = L.marker([obj.lat,obj.lng],{icon}).addTo(markersLayer);

                marker.bindPopup(popup,{maxWidth:650});

                let interval;

                marker.on('popupopen',()=>{
                    interval = setInterval(()=>{
                        let frame = document.getElementById(frameId);
                        if(frame){
                            frame.src = obj.reload_url + (obj.reload_url.includes("?")?"&":"?") + "t=" + Date.now();
                        }
                    },1000);
                });

                marker.on('popupclose',()=>{
                    clearInterval(interval);
                });

            } else if(obj.youtube_url){

                let yt = obj.youtube_url.replace("watch?v=","embed/");

                let popup = `
                <b>${obj.name}</b><br>
                ${desc}
                <br><br>
                <iframe width="300" height="200" src="${yt}" frameborder="0" allowfullscreen></iframe>`;

                L.marker([obj.lat,obj.lng],{icon}).addTo(markersLayer).bindPopup(popup);

            } else if(obj.camera_url){

                let img = obj.camera_url.replace(/&amp;/g,"&");

                let popup = `
                <b>${obj.name}</b><br>
                ${desc}
                <br><br>
                <img src="${img}" width="320">`;

                L.marker([obj.lat,obj.lng],{icon}).addTo(markersLayer).bindPopup(popup);

            } else {

                L.marker([obj.lat,obj.lng],{icon}).addTo(markersLayer)
                .bindPopup(`<b>${obj.name}</b><br>${desc}`);

            }

        });

    });
}

map.on('moveend', loadObjects);
loadObjects();
getTraffic();

// -------- FILTRY --------
[countryFilter,regionFilter,fKamera,fRadar,fSemafor,fUdalost, fTraffic].forEach(el=>{
    el.onchange=()=>{
        saveFilters();
        loadObjects();
		getTraffic();
    };
});
function highlightMarker(lat, lng){

    let circle = L.circleMarker([lat, lng], {
        radius: 15,
        color: "red",
        weight: 3,
        fillColor: "red",
        fillOpacity: 0.3
    }).addTo(map);

    // animace "puls"
    let grow = true;

    let interval = setInterval(()=>{
        let r = circle.getRadius();

        if(grow){
            r += 1;
            if(r > 25) grow = false;
        } else {
            r -= 1;
            if(r < 10) grow = true;
        }

        circle.setRadius(r);
    }, 50);

    // smaže se po 30s
    setTimeout(()=>{
        clearInterval(interval);
        map.removeLayer(circle);
    }, 30000);

}

// -------- AI --------
function askAI(){

    let q = document.getElementById("aiSearch").value;

    let box = document.getElementById("aiBox");
    let status = document.getElementById("aiStatus");
    let loader = document.getElementById("aiLoader");
    let responseBox = document.getElementById("aiResponse");

    box.style.display = "block";
    status.innerText = "🤖 Přemýšlím...";
    loader.style.display = "block";
    responseBox.innerHTML = "";

    fetch("/api/ai.php",{
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:"prompt="+encodeURIComponent(q)
    })
    .then(r=>r.json())
    .then(data=>{

        loader.style.display="none";

        if(!data.objects || data.objects.length === 0){
            status.innerText="❌ Nic nenalezeno";
            return;
        }

        status.innerText="✅ Hotovo";

        let text = `<b>📍 Zde máš co jsem našel v okolí:</b><br><br>`;
        let firstValid = null;

       setTimeout(()=>{
       box.style.display="none";
       },5000);

        data.objects.forEach(obj=>{

            if(!obj.lat || !obj.lng) return;

            let lat = parseFloat(obj.lat);
            let lng = parseFloat(obj.lng);

            if(isNaN(lat) || isNaN(lng)) return;

            if(!firstValid) firstValid = obj;

            highlightMarker(lat, lng);

            let typeIcon = "📍";
            if(obj.type==="Kamera") typeIcon = "📷";
            if(obj.type==="Radar") typeIcon = "🚨";
            if(obj.type==="Usek") typeIcon = "📏";
			

            text += `${typeIcon} <b>${obj.name}</b><br>`;
        });

        responseBox.innerHTML = text;

        if(data.base){
            let lat = parseFloat(data.base.lat);
            let lng = parseFloat(data.base.lng);

            if(!isNaN(lat) && !isNaN(lng)){
                map.setView([lat, lng], 12);
            }
        }

        if(firstValid){

            let lat = parseFloat(firstValid.lat);
            let lng = parseFloat(firstValid.lng);

            setTimeout(()=>{

                let nearestLayer = null;
                let minDist = Infinity;

                markersLayer.eachLayer(function(layer){

                    let pos = layer.getLatLng();

                    let dist = Math.sqrt(
                        Math.pow(pos.lat - lat, 2) +
                        Math.pow(pos.lng - lng, 2)
                    );

                    if(dist < minDist){
                        minDist = dist;
                        nearestLayer = layer;
                    }

                });

                if(nearestLayer){
                    nearestLayer.openPopup();
                }

            },300);
        }

    })
    .catch(err=>{
        loader.style.display="none";
        status.innerText="❌ Chyba AI";
        responseBox.innerHTML=err;
    });

}

// -------- TLACITKA --------
<?php if(isset($_SESSION['user_id'])): ?>

document.getElementById("addRadarBtn").onclick=()=>{
    addMode=true;
    addLineMode=false;
    alert("Klikni do mapy");
};

document.getElementById("addLineBtn").onclick=()=>{
    addLineMode=true;
    addMode=false;
    currentLine=[];
    if(tempLine){
        map.removeLayer(tempLine);
        tempLine=null;
    }
    alert("Klikáním kreslíš úsek\nENTER = uložit\nESC = zrušit");
};

<?php endif; ?>


// -------- CLICK --------
map.on("click",function(e){

    if(addMode){

        const name = prompt("Název:");
        if(!name) return;

        const type = prompt("Typ:","Radar");
        const description = prompt("Popis:");
        const camera_url = prompt("URL kamera:","");
        const youtube_url = prompt("URL YouTube:","");
        const reload_url = prompt("Reload URL:","");

        const formData = new FormData();

        formData.append("name",name);
        formData.append("type",type);
        formData.append("description",description);
        formData.append("lat",e.latlng.lat);
        formData.append("lng",e.latlng.lng);
        formData.append("camera_url",camera_url);
        formData.append("youtube_url",youtube_url);
        formData.append("reload_url",reload_url);
        formData.append("country", countryFilter.value);
        formData.append("region", regionFilter.value);

        fetch("/api/add_object.php",{method:"POST",body:formData})
        .then(res=>res.text())
        .then(msg=>{
            alert(msg);
            loadObjects();
        });

        addMode=false;
        return;
    }

    if(addLineMode){

        currentLine.push([e.latlng.lat,e.latlng.lng]);

        if(tempLine){
            map.removeLayer(tempLine);
        }

        tempLine = L.polyline(currentLine,{
            color:"red",
            weight:8
        }).addTo(map);

        return;
    }

});


// -------- KEYBOARD --------
document.addEventListener("keydown",function(e){

    if(e.key==="Enter" && addLineMode){

        if(currentLine.length < 2){
            alert("Musíš dát alespon 2 body");
            return;
        }

        fetch("/radar/api/add_segment.php",{
            method:"POST",
            body:new URLSearchParams({
                name:"Úsek",
                coordinates:JSON.stringify(currentLine)
            })
        });

        addLineMode=false;
        currentLine=[];

        if(tempLine){
            map.removeLayer(tempLine);
            tempLine=null;
        }

        loadObjects();
    }

    if(e.key==="Escape" && addLineMode){

        addLineMode=false;
        currentLine=[];

        if(tempLine){
            map.removeLayer(tempLine);
            tempLine=null;
        }

        alert("Zrušeno");
    }

});

</script>

</body>
</html>