import util from './lib/util';
import VERSION from '../h5-version';

const currentHostname = window.location.hostname;

//h5 domain
const H5_LOCAL_DOAMIN = "10.10"; //本地调试环境
const H5_DAILY_DOAMIN = "daily.yuantutech.com";
const H5_UAT_DOMAIN = "uat.yuantutech.com";
const H5_ONLINE_DOMAIN = "s.yuantutech.com";
const H5_ABTEST_DOMAIN = "test.yuantutech.com";
const H5_QD_DOMAIN = "m.guahao.jkqd.gov.cn"; //在 m.guahao.jkqd.gov.cn 域名下发送请求

//tms domain
let TMS_DAILY_DOAMIN = "//daily.yuantutech.com";
let TMS_UAT_DOMAIN = "//uat.yuantutech.com";
let TMS_ONLINE_DOMAIN = "//s.yuantutech.com";
let TMS_ABTEST_DOMAIN = "//test.yuantutech.com";

//api domain
const API_LOCAL_DOMAIN = "http://test.yuantutech.com";//本地调试域名指向
const API_DAILY_DOMAIN = "api.daily.yuantutech.com";
let API_UAT_DOMAIN = "route.uat.yuantutech.com";
let API_ONLINE_DOMAIN = "route.yuantutech.com";
const API_ABTEST_DOMAIN = "test.yuantutech.com";
const API_QD_DOMAIN = "m.guahao.jkqd.gov.cn";

//工单domain
const TICKTE_UAT_DOMAIN = 'https://daily.yuantutech.com:3201'
const TICKTE_ONLINE_DOMAIN = 'https://node.yuantutech.com:3201'

const AOLSEE_UAT_DOMAIN = '//aolsee.uat.ali.yuantutech.com/aolsee-web';
const AOLSEE_ONLINE_DOMAIN = '//aolsee.yuantutech.com/aolsee-web';

const SERVICE_UAT_DOMAIN = 'https://search.yuantutech.com';
const SERVICE_ONLINE_DOMAIN = 'https://search.yuantutech.com';

const SEARCH_UAT_DOMAIN = 'http://search.yuantutech.com';
const SEARCH_ONLINE_DOMAIN = 'http://search.yuantutech.com';

const IS_DAILY = currentHostname.indexOf(H5_DAILY_DOAMIN) != -1;
const IS_UAT = currentHostname.indexOf(H5_UAT_DOMAIN) != -1;
const IS_ONLINE = currentHostname.indexOf(H5_ONLINE_DOMAIN) != -1;
const IS_ABTEST = currentHostname.indexOf(H5_ABTEST_DOMAIN) != -1;
const IS_QD = currentHostname.indexOf(H5_QD_DOMAIN) != -1;

// if (util.isInYuantuApp() && util.version.lt(3, 5, 0)) {
//   API_UAT_DOMAIN = "api.uat.yuantutech.com";
//   API_ONLINE_DOMAIN = "api.yuantutech.com";
// }

let API_DOMAIN = API_ONLINE_DOMAIN;
let H5_DOMAIN = H5_ONLINE_DOMAIN;
let TICKET_DOMAIN = TICKTE_ONLINE_DOMAIN;
let TMS_DOMAIN = TMS_ONLINE_DOMAIN;
let AOLSEE_DOMAIN = AOLSEE_ONLINE_DOMAIN;
let SERVICE_DOMAIN = SERVICE_ONLINE_DOMAIN;
let SEARCH_DOMAIN = SEARCH_ONLINE_DOMAIN;

if(IS_DAILY){
  API_DOMAIN = API_DAILY_DOMAIN;
  H5_DOMAIN = H5_DAILY_DOAMIN;
  TICKET_DOMAIN = TICKTE_UAT_DOMAIN;
  TMS_DOMAIN = TMS_DAILY_DOAMIN;
  AOLSEE_DOMAIN = AOLSEE_UAT_DOMAIN;
  SEARCH_DOMAIN = SEARCH_UAT_DOMAIN;
  SERVICE_DOMAIN = SERVICE_UAT_DOMAIN;
}else if(IS_QD){
  API_DOMAIN = API_QD_DOMAIN;
  H5_DOMAIN = H5_ONLINE_DOMAIN;
  TICKET_DOMAIN = TICKTE_UAT_DOMAIN;
  AOLSEE_DOMAIN = AOLSEE_UAT_DOMAIN;
}else if(IS_UAT){
  API_DOMAIN = API_UAT_DOMAIN;
  // API_DOMAIN = API_ABTEST_DOMAIN;
  H5_DOMAIN = H5_UAT_DOMAIN;
  TICKET_DOMAIN = TICKTE_UAT_DOMAIN;
  TMS_DOMAIN = TMS_UAT_DOMAIN;
  AOLSEE_DOMAIN = AOLSEE_UAT_DOMAIN;
  SEARCH_DOMAIN = SEARCH_UAT_DOMAIN;
  SERVICE_DOMAIN = SERVICE_UAT_DOMAIN;
}else if(IS_ABTEST){
  API_DOMAIN = API_ABTEST_DOMAIN;
  H5_DOMAIN = H5_ABTEST_DOMAIN;
  TICKET_DOMAIN = TICKTE_UAT_DOMAIN;
  TMS_DOMAIN = TMS_ABTEST_DOMAIN;
  AOLSEE_DOMAIN = AOLSEE_UAT_DOMAIN;
  SEARCH_DOMAIN = SEARCH_UAT_DOMAIN;
  SERVICE_DOMAIN = SERVICE_UAT_DOMAIN;
}else{
  API_DOMAIN = API_ONLINE_DOMAIN;
  H5_DOMAIN = H5_ONLINE_DOMAIN;
  TICKET_DOMAIN = TICKTE_ONLINE_DOMAIN;
  TMS_DOMAIN = TMS_ONLINE_DOMAIN;
  AOLSEE_DOMAIN = AOLSEE_ONLINE_DOMAIN;
  SEARCH_DOMAIN = SEARCH_ONLINE_DOMAIN;
  SERVICE_DOMAIN = SERVICE_ONLINE_DOMAIN;
}
let PROTOCOL = 'https://'
let config = {
  VERSION,
  IS_DAILY,
  IS_UAT,
  IS_ONLINE,
  IS_ABTEST,
  PROTOCOL,
  API_DOMAIN,
  H5_DOMAIN,
  TMS_DOMAIN,
  TICKET_DOMAIN,
  AOLSEE_DOMAIN,
  SEARCH_DOMAIN,
  SERVICE_DOMAIN
};

export {
  VERSION,
  IS_DAILY,
  IS_UAT,
  IS_ONLINE,
  IS_ABTEST,
  API_DOMAIN,
  H5_DOMAIN,
  TMS_DOMAIN,
  TICKET_DOMAIN,
  AOLSEE_DOMAIN,
  SEARCH_DOMAIN,
  SERVICE_DOMAIN
}
export default config
