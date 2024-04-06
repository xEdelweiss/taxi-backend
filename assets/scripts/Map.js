import {decode} from '@googlemaps/polyline-codec';
import L from "leaflet";

class Map {
  map = null;
  markers = {};
  defaultZoom = TaxiConsts.MAP_ZOOM;

  constructor(containerId, zoom = TaxiConsts.MAP_ZOOM, latLng = TaxiConsts.MAP_CENTER) {
    this.defaultZoom = zoom;
    this.map = this._createMap(containerId, zoom, latLng);
  }

  static decodePolyline(polyline) {
    return decode(polyline)
      .map(([lat, lng]) => new L.LatLng(lat, lng));
  }

  static getIcon(type, scale = 1.2) {
    return type === 'user'
      ? Map.getUserIcon(scale)
      : Map.getDriverIcon(scale);
  }

  static getUserIcon(scale = 1.2) {
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

  static getDriverIcon(scale = 1.2) {
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

  move(latLng, zoom = this.defaultZoom) {
    if (latLng[0] === 0 && latLng[1] === 0) {
      return;
    }

    this.map.flyTo(new L.LatLng(...latLng), zoom, {
      animate: true,
      duration: 0.5,
    });
  }

  onClick(callback) {
    this.map.on('click', callback);
  }

  addMarker(id, marker) {
    this.markers[id] = marker;
    this._toggleVisibility(id);
  }

  removeMarker(id) {
    if (this.markers[id]) {
      this.markers[id].removeFrom(this.map);
      delete this.markers[id];
    }

    return null;
  }

  moveMarker(id, latLng, alignMap = false, duration = 0) {
    latLng ??= [0, 0];

    if (!this.markers[id]) {
      return;
    }

    duration = !this._isVisible(id) ? 0 : duration;

    if (duration === 0) {
      this.markers[id].setLatLng(new L.LatLng(...latLng));

      if (alignMap) {
        this.move(latLng);
      }
    } else {
      this.markers[id].slideTo(new L.LatLng(...latLng), {
        duration,
        keepAtCenter: alignMap,
      });
    }

    this._toggleVisibility(id);
  }

  replaceMarker(id, marker) {
    this.removeMarker(id);
    this.addMarker(id, marker);
  }

  hasMarker(id) {
    return this.markers[id] !== undefined;
  }

  getMarkersIds() {
    return Object.keys(this.markers);
  }

  _isVisible(id) {
    return this.map.hasLayer(this.markers[id])
      && !this.markers[id].getLatLng().equals(new L.LatLng(0, 0));
  }

  _toggleVisibility(id) {
    const marker = this.markers[id];

    if (marker instanceof L.Polyline) {
      marker.addTo(this.map);
      return;
    }

    const isZeroLatLng = marker.getLatLng().equals(new L.LatLng(0, 0));

    if (!isZeroLatLng && !this.map.hasLayer(marker)) {
      marker.addTo(this.map);
    }

    if (isZeroLatLng && this.map.hasLayer(marker)) {
      marker.removeFrom(this.map);
    }
  }

  fitBounds(bounds) {
    this.map.fitBounds(bounds, {
      padding: [150, 150],
    });
  }

  _createMap(containerId, zoom, latLng) {
    const map = L.map(containerId).setView(latLng, zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: `<a target='_blank' href='https://www.flaticon.com/authors/freepik'>Freepik</a> <span aria-hidden="true">|</span> &copy; <a target='_blank' href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a>`
    }).addTo(map);

    return map;
  }
}

export default Map;
