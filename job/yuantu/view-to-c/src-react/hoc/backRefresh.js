import React from 'react';

export default function (ComponentClass) {
  return class BackRefresh extends ComponentClass {

    componentDidMount() {
      super.componentDidMount();
      const superComponentDidMount = super.componentDidMount.bind(this);
      window.addEventListener('pageshow', superComponentDidMount);
    }

    render() {
      return super.render();
    }
  }
}
