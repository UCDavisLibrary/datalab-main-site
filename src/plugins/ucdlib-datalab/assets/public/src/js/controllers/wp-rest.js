export default class WpRest {
  constructor(host){
    (this.host = host).addController(this);
    this.nonce = host.wpNonce || '';
    this.namespace = host.restNamespace || '';
  }
}
