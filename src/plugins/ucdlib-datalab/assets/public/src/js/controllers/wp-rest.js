export default class WpRest {
  constructor(host){
    (this.host = host).addController(this);

    this.cache = {};
    this.requestsInProgress = {};
  }

  clearCache($key){
    if( $key ) {
      delete this.cache[$key];
      delete this.requestsInProgress[$key];
    } else {
      this.cache = {};
      this.requestsInProgress = {};
    }
  }

  async get(path, params={}) {
    try {
      const data = await this._fetch(path, {params});
      return {
        status: 'success',
        data
      }
    } catch (error) {
      return {
        status: 'error',
        error
      }
    }
  }

  async post(path, payload={}, params={}) {
    try {
      const data = await this._fetch(path, {method: 'POST', payload, params});
      return {
        status: 'success',
        data
      }
    } catch (error) {
      return {
        status: 'error',
        error
      }
    }
  }

  async put(path, payload={}, params={}) {
    try {
      const data = await this._fetch(path, {method: 'PUT', payload, params});
      return {
        status: 'success',
        data
      }
    } catch (error) {
      return {
        status: 'error',
        error
      }
    }
  }

  _fetch(path, options={}) {
    let url = this.getApiUrl(path);
    let cacheKey = this._getCacheKey(url, options);
    let cache = this.cache[cacheKey];

    if( cache ) {
      return cache;
    }

    if( this.requestsInProgress[cacheKey] ) {
      return this.requestsInProgress[cacheKey];
    }

    let headers = {
      'Content-Type': 'application/json'
    };

    if( this.host.wpNonce ) {
      headers['X-WP-Nonce'] = this.host.wpNonce;
    }

    let body = null;
    if( options.payload ) {
      body = JSON.stringify(options.payload);
    }

    let request = fetch(url, {
      method: options.method,
      headers,
      body
    }).then(res => {
      if( res.status >= 400 ) {
        delete this.requestsInProgress[cacheKey];
        throw new Error(`Error fetching ${url}: ${res.status}`);
      }
      return res.json();
    }).then(data => {
      delete this.requestsInProgress[cacheKey];
      this.cache[cacheKey] = data;
      return data;
    });

    this.requestsInProgress[cacheKey] = request;
    return request;
  }

  getApiUrl(path) {
    return `${window.location.origin}/wp-json/${this.host.restNamespace}/${path}`;
  }

  _getCacheKey(url, options) {
    let key = url;
    if( options.params ) {
      key += JSON.stringify(options.params);
    }
    if( options.payload ) {
      key += JSON.stringify(options.payload);
    }
    return key;
  }

}
