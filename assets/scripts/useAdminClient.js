import Alpine from 'alpinejs';
import L from 'leaflet';
import TaxiMap from './TaxiMap.js';
import TaxiUtils from './TaxiUtils.js';

export default function useAdminClient() {
  Alpine.data('adminClient', () => ({
    showControls: true,
    users: [],
    drivers: [],
    async init() {
      this.$nextTick(() => this._initMap('admin-map'));

      await fetch('/debug/get-users')
        .then(response => response.json())
        .then(({data}) => {
          this.users = data.users.map(item => ({...item, status: 'No order'}));
          this.drivers = data.drivers.map(item => ({...item, status: 'No order'}));

          this._redrawMarkers();
        });

      if (this.users.length === 0) {
        await this.addUser();
      }

      if (this.drivers.length === 0) {
        await this.addDriver();
      }

      this.selectUser(this.users[0].phone);
      this.selectDriver(this.drivers[0].phone);
    },
    _initMap(id) {
      this.map = TaxiMap.createMap(id, 14);

      const coordsPopup = L.popup();
      function onMapClick(e) {
        coordsPopup
          .setLatLng(e.latlng)
          .setContent(e.latlng.toString())
          .openOn(this.map);
      }
      this.map.on('click', onMapClick);
    },
    _redrawMarkers() {
      this.users.forEach(user => {
        this._moveMarker('user', user);
      });
      this.drivers.forEach(driver => {
        this._moveMarker('driver', driver);
      });
    },
    _moveMarker(type, user) {
      if (!user.coordinates || !this.map) {
        console.log('no coords or map');
        return;
      }

      const latLng = [
        user.coordinates.latitude,
        user.coordinates.longitude,
      ];

      if (!user.marker) {
        user.marker = L.marker(latLng, {
            icon: TaxiMap.getIcon(type),
          })
          .bindTooltip(TaxiUtils.formatPhone(user.phone), {
            permanent: false,
            direction: 'right',
            offset: [16, -20],
            className: 'font-semibold text-sm bg-white text-gray-800 p-2 rounded-md shadow-md',
          })
          .on('click', () => {
            if (type === 'user') {
              this.selectUser(user.phone);
            } else {
              this.selectDriver(user.phone);
            }
          })
          .addTo(this.map);
      } else {
        user.marker.setLatLng(new L.LatLng(...latLng));
      }
    },
    addUser: async function() {
      return this._makeUser('user')
        .then(data => {
          this.users.push({phone: data.phone, status: 'No order'});
        });
    },
    addDriver: async function() {
      return this._makeUser('driver')
        .then(data => {
          this.drivers.push({phone: data.phone, status: 'No order'});
        });
    },
    _makeUser: function(type) {
      return fetch('/debug/add-user', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({type}),
      })
        .then(response => response.json())
        .then(({data}) => data);
    },
    removeUser: function(phone) {
      this._deleteUser(phone)
        .then(() => {
          this.users = this.users.filter(user => user.phone !== phone);
          this.$dispatch('user-removed', {phone});
          console.log('Event:', 'user-removed', {phone});
        });
    },
    removeDriver: function(phone) {
      this._deleteUser(phone)
        .then(() => {
          this.drivers = this.drivers.filter(driver => driver.phone !== phone);
          this.$dispatch('driver-removed', {phone});
          console.log('Event:', 'driver-removed', {phone});
        });
    },
    _deleteUser: function(phone) {
      return fetch('/debug/remove-user', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({phone}),
      })
        .then(response => response.json())
        .then(({data}) => data);
    },
    selectUser: function(phone) {
      this.$dispatch('user-selected', {phone});
      console.log('Event:', 'user-selected', {phone});
    },
    selectDriver: function(phone) {
      this.$dispatch('driver-selected', {phone});
      console.log('Event:', 'driver-selected', {phone});
    },
  }));
}
