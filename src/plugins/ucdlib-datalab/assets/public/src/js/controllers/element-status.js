import { html, css, svg } from 'lit';

/**
 * @description Controller for displaying loading/error status of an element
 */
export default class ElementStatus {

  constructor(host, kwargs={}){
    (this.host = host).addController(this);

    this.loadingHeight = kwargs.loadingHeight || 'auto';
    this.errorMessage = kwargs.errorMessage || 'An unexpected error occurred.';
  }

  static get styles() {

    const custom = css`
      .loading {
          display: flex;
          justify-content: center;
        }
      .loading .loading-icon {
        animation: spin 2s linear infinite, opacity-pulse 2s linear infinite;
        width: calc(3vw);
        height: calc(3vw);
        max-width: 50px;
        max-height: 50px;
        min-width: 20px;
        min-height: 20px;
        color: #022851;
      }
      .loading-icon svg {
        display: block;
        width: 100%;
        height: 100%;
        fill: currentColor;
      }
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      @keyframes opacity-pulse {
        0% { opacity: 0.4; }
        25% { opacity: 0.5; }
        50% { opacity: 1; }
        75% { opacity: 0.5; }
        100% { opacity: 0.4; }
      }
    `;
    return custom;
  }

  setLoadingHeight(height) {
    this.loadingHeight = height;
    this.host.requestUpdate();
  }

  renderLoading() {
    return html`
      <div class='loading' style='height:${this.loadingHeight}'>
        <div class='loading-icon'>
        ${svg`
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M222.7 32.1c5 16.9-4.6 34.8-21.5 39.8C121.8 95.6 64 169.1 64 256c0 106 86 192 192 192s192-86 192-192c0-86.9-57.8-160.4-137.1-184.1c-16.9-5-26.6-22.9-21.5-39.8s22.9-26.6 39.8-21.5C434.9 42.1 512 140 512 256c0 141.4-114.6 256-256 256S0 397.4 0 256C0 140 77.1 42.1 182.9 10.6c16.9-5 34.8 4.6 39.8 21.5z"/></svg>
        `}
        </div>
      </div>
    `;
  }

  renderError(){
    return html`
      <div class='error'>
        <div>${this.errorMessage}</div>
      </div>
    `;
  }

}
