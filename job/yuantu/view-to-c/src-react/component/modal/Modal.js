import React from 'react';
import './Modal.less';

export default class Modal extends React.Component {

  static defaultProps = {
    show: false,
    position: 'bottom',
  };

  static propTypes = {
    show: React.PropTypes.bool,
    position: React.PropTypes.string,
    onCancel: React.PropTypes.func,
    onConfirm: React.PropTypes.func,
    header: React.PropTypes.element,
  };

  state = {
    show: false
  };

  componentWillReceiveProps(next) {
    this.setState({
      show: next.show
    });
  }

  close = () => {
    this.props.onCancel();
    this.setState({
      show: false,
    });
  };

  render() {
    const {children, header} = this.props;
    const {show} = this.state;
    if (show) {
      return (
        <div
          className="modal-container"
          onClick={this.close}
        >
          <div className="modal-main" onClick={(e) => {e.stopPropagation()}}>
            {header ? header : null}
            {children}
          </div>
        </div>
      );
    }
    return null;
  }
}
