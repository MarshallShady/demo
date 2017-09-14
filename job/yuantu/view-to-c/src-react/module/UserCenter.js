//无缓存数据
import JSONPAsyncData from '../lib/JSONPAsyncData'
import util from '../lib/util';
import md5 from '../lib/md5'
//优先读取缓存数据
import JSONPCacheAsyncData from '../lib/JSONPCacheAsyncData'
import config from '../config'
import H5_VERSION from '../../h5-version';

const isInYuantuApp = util.isInYuantuApp();

const IS_ONLINE = config.IS_ONLINE;
const API_DOMAIN = config.API_DOMAIN;
const PROTOCOL = config.PROTOCOL;

const deviceInfo = {
  invokerChannel: 'H5',
  invokerDeviceType: isInYuantuApp ? 'yuantuApp' : (util.isWeixin() ? 'weixin' : 'others'),
  invokerAppVersion: H5_VERSION
};

const query = util.query();
const uid = query.unionId || '';

function getAPIUri( path ){
  return API_DOMAIN.indexOf("http") == 0 ? API_DOMAIN+path : PROTOCOL+API_DOMAIN+path;
}


export default {

}
