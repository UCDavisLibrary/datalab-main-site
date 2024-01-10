import { html, css } from 'lit';

export function styles() {
  const elementStyles = css`
    :host {
      display: block;
    }
  `;

  return [elementStyles];
}

// main render function for element
export function render() {
  if ( this.fetchStatus === 'loading' ) {
    return renderLoading.call(this);
  }
  if ( this.fetchStatus === 'loaded' ) {
    return renderLoaded.call(this);
  }
  return renderError.call(this);
}

function renderLoaded(){
  return html`<p>loaded</p>`
}

function renderLoading(){
  return html`<p>loading</p>`
}

function renderError(){
  return html`<p>error!</p>`
}
