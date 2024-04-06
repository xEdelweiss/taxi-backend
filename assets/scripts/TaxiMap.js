import {decode} from '@googlemaps/polyline-codec';

class TaxiMap {
  createMap(containerId, zoom = TaxiConsts.MAP_ZOOM, latLng = TaxiConsts.MAP_CENTER) {
    const map = L.map(containerId).setView(latLng, zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: `<a target='_blank' href='https://www.flaticon.com/authors/freepik'>Freepik</a> <span aria-hidden="true">|</span> &copy; <a target='_blank' href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a>`
    }).addTo(map);

    return map;
  }

  decodePolyline(polyline) {
    console.log('DECODED POLYLINE', decode(polyline));

    return decode(polyline)
    .map(([lat, lng]) => new L.LatLng(lat, lng));
  }

  moveMap(map, latLng, zoom = 16) {
    map.flyTo(new L.LatLng(...latLng), zoom, {
      animate: true,
      duration: 0.5,
    })
  }

  getIcon(type, scale = 1.2) {
    return type === 'user'
      ? this.getUserIcon(scale)
      : this.getDriverIcon(scale);
  }

  getUserIcon(scale = 1.2) {
    return L.icon({
      iconUrl: TaxiConsts.USER_ICON_URL,
      // shadowUrl: 'leaf-shadow.png',

      iconSize: [32 * scale, 32 * scale], // size of the icon
      // shadowSize:   [50, 64], // size of the shadow
      iconAnchor: [16 * scale, 34 * scale], // point of the icon which will correspond to marker's location
      // shadowAnchor: [4, 62],  // the same for the shadow
      popupAnchor: [0, 0] // point from which the popup should open relative to the iconAnchor
    });
  }

  getDriverIcon(scale = 1.2) {
    return L.icon({
      iconUrl: TaxiConsts.DRIVER_ICON_URL,
      // shadowUrl: 'leaf-shadow.png',

      iconSize: [32 * scale, 32 * scale], // size of the icon
      // shadowSize:   [50, 64], // size of the shadow
      iconAnchor: [16 * scale, 34 * scale], // point of the icon which will correspond to marker's location
      // shadowAnchor: [4, 62],  // the same for the shadow
      popupAnchor: [0, 0] // point from which the popup should open relative to the iconAnchor
    });
  }
}

export default new TaxiMap();
