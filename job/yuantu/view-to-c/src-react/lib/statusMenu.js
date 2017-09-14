import React from 'react';
import StatusBlock from '../component/statusBlock/statusBlock';

export default function(status) {
  status = status ? status.toString() : '';
  switch(status) {
    case "426":
      StatusBlock.show();
      break;
    default:
      break;
  }
}
