import JSONPData from '../lib/JSONPData';
import { TMS_DOMAIN } from '../config';
import 'isomorphic-fetch';

//获取当前ip
async function getIP() {
  const response = await fetch(`${TMS_DOMAIN}/tms/h5/get-ip.php`);
  const ip = await response.text();
  return ip;
}

//根据ip获取经纬度
export default async function getLocation() {
  const ip = await getIP();
  const response = new JSONPData(`https://apis.map.qq.com/ws/location/v1/ip`, {
    output: 'jsonp',
    key: 'TKUBZ-D24AF-GJ4JY-JDVM2-IBYKK-KEBCU',
    ip,
  });
  const data = await response.fetch();
  if (data && data.result && data.result.location) {
    return {
      lat: data.result.location.lat,
      lng: data.result.location.lng
    }
  }
  return null;
}
