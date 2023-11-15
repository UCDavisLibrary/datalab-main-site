import { LitElement } from 'lit';
import * as templates from "./ucdlib-dl-jobs-board-admin.tpl.js";

import '@ucd-lib/theme-elements/ucdlib/ucdlib-pages/ucdlib-pages.js'

export default class UcdlibDlJobsBoardAdmin extends LitElement {

  static get properties() {
    return {
      wpNonce: {type: String, attribute: 'wp-nonce'},
      restNamespace: {type: String, attribute: 'rest-namespace'},
      logoUrl: {type: String, attribute: 'logo-url'},
      logoWidth: {type: String, attribute: 'logo-width'},
      pages: {state: true},
      page: {state: true}
    }
  }

  static get styles() {
    return templates.styles();
  }

  constructor() {
    super();
    this.render = templates.render.bind(this);
    this.renderPendingRequests = templates.renderPendingRequests.bind(this);
    this.renderActiveListings = templates.renderActiveListings.bind(this);
    this.renderExpiredListings = templates.renderExpiredListings.bind(this);
    this.renderSettings = templates.renderSettings.bind(this);
    this.renderLoading = templates.renderLoading.bind(this);

    this.wpNonce = '';
    this.restNamespace = '';
    this.logoUrl = '';
    this.logoWidth = '';

    this.pages = [
      {id: 'pending', name: 'Pending Requests', cb: this.renderPendingRequests},
      {id: 'active', name: 'Active Listings', cb: this.renderActiveListings},
      {id: 'expired', name: 'Expired Listings', cb: this.renderExpiredListings},
      {id: 'settings', name: 'Settings', cb: this.renderSettings},
      {id: 'loading', name: 'Loading', cb: this.renderLoading, noNav: true}
    ];
    this.page = this.pages[0].id;
  }

  _onPageChange(e) {
    const i = e.detail.location[0];
    this.page = this.pages[i].id;
  }

}

customElements.define('ucdlib-dl-jobs-board-admin', UcdlibDlJobsBoardAdmin);
