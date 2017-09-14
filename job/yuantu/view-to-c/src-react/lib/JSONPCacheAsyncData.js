
import JSONPAsyncData from './JSONPAsyncData'
import util from './util'
import md5 from './md5'
import cache from './cache'
import H5_VERSION from '../../h5-version';

const isInYuantuApp = util.isInYuantuApp();

//jsonp的异步请求
//增加了一个 SUCCESS 事件
export default class JSONPCacheAsyncData extends JSONPAsyncData{

  constructor(url, param, timeout){
    let uid = util.getUID() + "";
    //缓存数据增加用户id签名，避免重复
    let key = md5(url + util.flat(param || {}) + uid);

    super(url, param);

    this.key = key;
    this.timeout = timeout;

    //如果回调正确保存数据
    this.onSuccess(( result )=>{
      try{
        cache.set(key, JSON.stringify(result));
      }catch(e){
        console.log("缓存写入错误");
      }
    })
  }
  /**
    使用 async await 语法不能获取缓存数据
  **/
  fetch(){
    let resultPromise = super.fetch()
    try{
      //从缓存中拿数据
      let result = JSON.parse(cache.get(this.key))
      if( result ){
        // emit 数据
        this.emit(this.COMPLETE, result, this.param)
        //this.emit(this.SUCCESS, result);
      }
    }catch(e){
      // reject(e)
      console.log("缓存错误，丢弃")
    }
    return resultPromise
  }

}
