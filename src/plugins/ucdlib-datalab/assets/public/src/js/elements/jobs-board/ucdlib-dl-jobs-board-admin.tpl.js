import { html, css } from 'lit';

import normalize from "@ucd-lib/theme-sass/normalize.css.js";
import baseHtml from "@ucd-lib/theme-sass/1_base_html/_index.css.js";
import baseClass from "@ucd-lib/theme-sass/2_base_class/_index.css.js";
import oBox from "@ucd-lib/theme-sass/3_objects/_index.css.js";
import panel from "@ucd-lib/theme-sass/4_component/_panel.css.js";
import brandColors from "@ucd-lib/theme-sass/4_component/_category-brand.css.js";
import layouts from "@ucd-lib/theme-sass/5_layout/_index.css.js";
import spaceUtils from "@ucd-lib/theme-sass/6_utility/_u-space.css.js";

export function styles() {
  const elementStyles = css`
  :host {
    display: block;
    background-color: #fff;
    color: #000;
    margin-right: 1rem;
    margin-top: 1rem;
    padding-top: 2rem;
    padding-bottom: 2rem;
    line-height: 1.618;
    font-size: 1rem;
    font-family: "proxima-nova", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
  }
  .heading-container {
    display: flex;
    align-items: center;
  }
  .heading-container img {
    margin-right: 1rem;
  }
  @media screen and (max-width: 450px) {
    .heading-container img {
      display: none;
    }
  }
  .loading-icon {
    display: flex;
    justify-content: center;
  }
  .loading-icon ucdlib-icon {
    animation: spin 2s linear infinite, opacity-pulse 2s linear infinite;
    width: calc(3vw);
    height: calc(3vw);
    max-width: 50px;
    max-height: 50px;
    color: #022851;
  }
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(359deg); }
  }
  @keyframes opacity-pulse {
    0% { opacity: 0.4; }
    25% { opacity: 0.5; }
    50% { opacity: 1; }
    75% { opacity: 0.5; }
    100% { opacity: 0.4; }
  }

  `;

  return [
    normalize,
    baseHtml,
    baseClass,
    oBox,
    panel,
    brandColors,
    layouts,
    spaceUtils,
    elementStyles
  ];
}

export function render() {
return html`
  <div class='l-container'>
    <div class='heading-container'>
      ${this.logoUrl ? html`<img src=${this.logoUrl} width=${this.logoWidth} />` : html``}
      <h2 class='heading--weighted-underline'><span class='heading--weighted--weighted'>Jobs Board</span></h2>
    </div>
    <div class='l-basic--flipped'>
      <div class='l-content'>
        <div class='panel o-box'>
          <ucdlib-pages selected="page-${this.page}">
            ${this.pages.map(page => html`
              <div id="page-${page.id}">
                ${page.cb()}
              </div>
            `)}
          </ucdlib-pages>
        </div>
      </div>
      <div class='l-sidebar-second'>
        <div class='panel o-box'>
          <ucd-theme-subnav @item-click=${this._onPageChange}>
            ${this.pages.filter(p => !p.noNav).map(page => html`
              <a>${page.name}</a>
            `)}
          </ucd-theme-subnav>
        </div>
      </div>
    </div>
  </div>
`;}

export function renderPendingRequests(){
  return html`
    <h3>Pending Requests</h3>
    <p>These are the pending requests</p>
  `;
}

export function renderActiveListings(){
  return html`
    <h3>Active Listings</h3>
    <p>These are the active listings</p>
  `;
}

export function renderExpiredListings(){
  return html`
    <h3>Expired Listings</h3>
    <p>These are the expired listings</p>
  `;
}

export function renderSettings(){
  return html`
    <h3>Settings</h3>
    <p>These are the settings</p>
  `;
}

export function renderLoading(){
  return html`
    <div class='loading-icon'>
      <ucdlib-icon icon="ucd-public:fa-circle-notch"></ucdlib-icon>
    </div>
  `;
}
