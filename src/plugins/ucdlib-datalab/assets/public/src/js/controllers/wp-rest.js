export default class WpRest {
  constructor(host){
    (this.host = host).addController(this);

    this.cache = {};
    this.requestsInProgress = {};
  }

  clearCache($key){
    if( $key ) {
      if ( this.cache[$key] ) delete this.cache[$key];
      if ( this.requestsInProgress[$key] ) delete this.requestsInProgress[$key];
    } else {
      this.cache = {};
      this.requestsInProgress = {};
    }
  }

  async get(path, params={}) {
    try {
      const data = await this._fetch(path, {params, method: 'GET'});
      return {
        status: 'success',
        data: data.data,
        cacheKey: data.cacheKey
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
        data: data.data,
        cacheKey: data.cacheKey
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
        data: data.data,
        cacheKey: data.cacheKey
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
    if ( Object.keys(options.params || {}).length ) {
      url += `?${new URLSearchParams(options.params).toString()}`;
    }
    if ( !options.method ) {
      options.method = 'GET';
    }
    let cacheKey = this._getCacheKey(url, options);
    let cache = this.cache[cacheKey];

    if( options.method === 'GET' && cache ) {
      return {
        data: cache,
        cacheKey
      };
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

      return {data, cacheKey};
    });

    this.requestsInProgress[cacheKey] = request;
    return request;
  }

  getApiUrl(path) {
    return `${window.location.origin}/wp-json/${this.host.restNamespace}/${path}`;
  }

  _getCacheKey(url, options={}) {
    const u = url.split(`${this.host.restNamespace}/`)[1];
    if ( !options.method ) {
      options.method = 'GET';
    }
    let key = `${options.method}:${u}`;
    if( options.params ) {
      const p = new URLSearchParams(options.params);
      p.sort();
      key += p.toString();
    }
    if( options.payload ) {
      key += JSON.stringify(options.payload);
    }
    return key;
  }

}
