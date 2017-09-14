import React, {PropTypes} from 'react';
import {Swiper, Slide} from 'react-dynamic-swiper';
import './swiper.less'
import 'react-dynamic-swiper/lib/styles.css';
import Aolsee from '../../module/Aolsee'
import util from '../../lib/util';

export default class Swipe extends React.Component {

  static propTypes = {
    banners: PropTypes.array,
    unionId: PropTypes.string
  };

  constructor(props) {
    super(props)
    this.adPv = {
      adIds: []
      ,adIndex: 0
      ,length: 0
      ,isShow: true
    }
    const query = util.query();
    this.unionId = query.unionId || '';
  }

  componentDidMount() {
    window.addEventListener('scroll', this.changeScroll.bind(this), false)
  }

  changeScroll(e) {
    this.adPv.isShow = (window.scrollY < 200) ? true : false
  }

  shouldComponentUpdate(next) {
    return next.banners != this.props.banners;
  }

  item(data, i) {
    let {redirecUrl,imgUrl} = data
    redirecUrl = redirecUrl ? redirecUrl : ''
    return (
      <Slide key={data.imgUrl + 'i'} className="swiper-img-box">
        { redirecUrl == '' ? <a data-spm={i} >
          <img src={imgUrl}/>
        </a> : <a data-spm={i} href={`${redirecUrl}&target=_blank`}>
          <img src={imgUrl}/>
        </a> }

      </Slide>
    );
  }

  slideEnd() {
    const {adIndex,adIds,isShow} = this.adPv
    ,{unionId} = this
    ,invokerDeviceId = window.localStorage.adDeviceId || ''
    ,adId = adIds[adIndex] || ''
    if(isShow){
      Aolsee.setAdPv(adId, invokerDeviceId, unionId)
        .subscribe(this)
        .fetch();
    }
    
  }
  onSuccess(result) {
    if(!window.localStorage.adDeviceId) {
      window.localStorage.adDeviceId = result.data
    }
  }

  slidePrev() {
    const {adPv} = this
    if(--adPv.adIndex < 0) { 
      adPv.adIndex = adPv.length - 1
    }
  }
  slideNext() {
    const {adPv} = this
    adPv.adIndex = (adPv.adIndex + 1)%adPv.length
  }

  render() {
    const {banners} = this.props;
    const result = banners.filter(z => z.imgUrl);
    const {adIds} = this.adPv

    if (result.length > 0) {
      if(adIds.length == 0) {
        result.forEach(function(item) {
          adIds.push( item.adId )
        });
        this.adPv.length = adIds.length
      }
      return (
        <Swiper
          swiperOptions={{
            slidesPerView: 'auto',
            loop: true,
            autoplay: 5000,
            autoplayDisableOnInteraction: false,
            onSlideChangeEnd: () => this.slideEnd(),
            onSlideNextEnd: () => this.slideNext(),
            onSlidePrevEnd: () => this.slidePrev()
          }}
          pagination={true}
          navigation={false}
        >
          {
            result.map((z, i) => this.item(z, i))
          }
        </Swiper>
      );
    }
    return (
      <div className="swiper-empty-box">
        <img src="https://image.yuantutech.com/i4/b2bfb00af68aa5566ae3de988bd8b454-750-388.png"/>
      </div>
    );
  }

}
