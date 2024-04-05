class TaxiApi {
  async login(phone) {
    if (!phone) {
      throw new Error('No phone provided');
    }

    const {token} = await fetch('/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        phone,
        password: '!password!',
      }),
    }).then(response => response.json());

    const userLocation = await this._fetchLocation(phone);

    return {
      token,
      latLng: userLocation?.coordinates
        ? [userLocation.coordinates.latitude, userLocation.coordinates.longitude]
        : TaxiConsts.DEFAULT_USER_LAT_LNG,
    }
  }

  async fetchCoordsByAddress(address, token) {
    if (!token) {
      throw new Error('No token provided');
    }

    const {data} = await fetch('/api/geolocation/coordinates', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        address,
      }),
    }).then(response => response.json());

    if (!data) {
      throw new Error('Address not found');
    }

    return [data.latitude, data.longitude];
  }

  async fetchAddressByCoords(latLng, token) {
    if (!token) {
      throw new Error('No token provided');
    }

    const {data} = await fetch('/api/geolocation/addresses', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        latitude: latLng[0],
        longitude: latLng[1],
      }),
    }).then(response => response.json());

    return data.address;
  }

  saveLocation(latLng, token) {
    if (!token) {
      throw new Error('No token provided');
    }

    return fetch('/api/tracking/locations', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        latitude: latLng[0],
        longitude: latLng[1],
      }),
    });
  }

  async _fetchLocation(phone) {
    const {data: {locations}}  = await fetch(`/debug/last-location?phones[]=${phone}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    }).then(response => response.json());

    return locations.find(location => location.phone === phone);
  }
}

export default new TaxiApi();
