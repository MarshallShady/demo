var IS_SESSION_STORGEl = window.sessionStorage
/**
 cache.set( key, value )
 cache.get( key )
 */
module.exports = {
  cacheMap: {},
  set: function set(key, value) {

    if (IS_SESSION_STORGEl) {
      if( typeof value ) {
        value = 'isObject' + JSON.stringify( value )
      }
      return window.sessionStorage.setItem(key, value)
    }
    return null
  },
  get: function get(key) {
    let result = null
    if (IS_SESSION_STORGEl) {
       result = window.sessionStorage.getItem(key)
       if( result.indexOf( 'isObject' ) != -1 ) {
         result = JSON.parse( result.slice( 8 ) )
       }
    }
    return result
  },
  remove: function remove(key) {
    if (IS_SESSION_STORGEl) {
      return window.sessionStorage.removeItem(key)
    }
    return null
  }
}
