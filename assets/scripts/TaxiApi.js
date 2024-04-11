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
    const orders = await this.fetchOrders(token);

    return {
      token,
      latLng: userLocation?.coordinates
        ? [userLocation.coordinates.latitude, userLocation.coordinates.longitude]
        : TaxiConsts.DEFAULT_USER_LAT_LNG,
      order: orders[0] || null,
    }
  }

  async fetchCoordsByAddress(address, token) {
    if (!token) {
      throw new Error('No token provided');
    }

    const data = await fetch('/api/geolocation/coordinates', {
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

    const data = await fetch('/api/geolocation/addresses', {
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

  async fetchRoute(fromLatLng, fromAddress, toLatLng, toAddress, token) {
    if (!token) {
      throw new Error('No token provided');
    }

    const data = await fetch('/api/navigation/routes', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        start: {
          latitude: fromLatLng[0],
          longitude: fromLatLng[1],
          address: fromAddress,
        },
        end: {
          latitude: toLatLng[0],
          longitude: toLatLng[1],
          address: toAddress,
        },
      }),
    }).then(response => response.json());

    return data;
  }

  async fetchEstimation(fromLatLng, fromAddress, toLatLng, toAddress, token) {
    if (!token) {
      throw new Error('No token provided');
    }

    const data = await fetch('/api/cost/estimations', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        start: {
          latitude: fromLatLng[0],
          longitude: fromLatLng[1],
          address: fromAddress,
        },
        end: {
          latitude: toLatLng[0],
          longitude: toLatLng[1],
          address: toAddress,
        },
      }),
    }).then(response => response.json());

    return data;
  }

  async createOrder(fromLatLng, fromAddress, toLatLng, toAddress, token) {
    if (!token) {
      throw new Error('No token provided');
    }

    const data = await fetch('/api/trip/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        start: {
          latitude: fromLatLng[0],
          longitude: fromLatLng[1],
          address: fromAddress,
        },
        end: {
          latitude: toLatLng[0],
          longitude: toLatLng[1],
          address: toAddress,
        },
      }),
    }).then(response => response.json());

    return data;
  }

  async payOrder(orderId, token) {
    if (!token) {
      throw new Error('No token provided');
    }

    const data = await fetch(`/api/payment/holds`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        order_id: orderId,
      }),
    });

    return data;
  }

  async _fetchLocation(phone) {
    const {items: locations} = await fetch(`/debug/last-location?phones[]=${phone}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    }).then(response => response.json());

    return locations.find(location => location.phone === phone);
  }

  async fetchOrders(token) {
    const {items: orders} = await fetch(`/api/trip/orders`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    }).then(response => response.json());

    return orders;
  }

  async fetchOrder(token, orderId) {
    const order = await fetch(`/api/trip/orders/${orderId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    }).then(response => response.json());

    return order;
  }
}

export default new TaxiApi();
