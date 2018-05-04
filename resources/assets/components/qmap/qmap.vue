<template>
    <div class="container" :style="{width: width, height: height}">
        <div ref="mapContainer" :style="{width: width, height: height}"></div>
        <slot/>
    </div>
</template>

<script>
    import QmapManager from './qmap-manager'
    /**
     * 腾讯地图容器组件
     */
    export default {
        name: "qmap",
        props: {
            width: {type: String, default: '100%'}, // 地图宽度
            height: {type: String, default: '100%'}, // 地图高度
            center: { type: Array, default: () => [114.05956, 22.54286] }, // 地图中心点, 一个数组, 第一个元素是经度, 第二个元素是纬度, 默认为深圳市的经纬度
            zoom: { type: Number, default: 10}, // 缩放级别, 默认为10
            zoomControl: {type: Boolean, default: false}, // 是否启用缩放控件, 默认不使用
            panControl: {type: Boolean, default: false}, // 是否使用平移控件, 默认不使用
            autoBoundsOnMarker: {type: Boolean, default: true}, // 是否在设置marker后重置地图边界
        },
        data(){
            let manager = new QmapManager();
            return {
                manager: manager, // 地图组件管理容器, 用于通过依赖注入的方式调用其他组件
                qmap: null, // 腾讯地图实例
                infoWindow: null, // 全局的infoWindow, infoWindow暂定同时只能弹出一个
            }
        },
        provide: function () {
            // 使用依赖注入将map对象暴露给子组件, 以便于子组件访问map对象
            return {
                manager: this.manager,
            }
        },
        methods: {
            initQmap(){
                // 初始化地图
                if(!this.$refs.mapContainer){
                    // 如果地图容器尚未挂载, 延迟300毫秒后再执行
                    setTimeout(() => {this.initQmap()}, 100);
                }else{
                    let center = this.center;
                    this.qmap = new qq.maps.Map(this.$refs.mapContainer, {
                        center: new qq.maps.LatLng(center[1], center[0]), // 地图的中心地理坐标。
                        zoom: this.zoom, // 缩放级别
                        zoomControl: false, // 缩放控件
                        panControl: false, // 平移控件
                        mapTypeControl: false, // 地图类型切换
                    });
                    // 初始化infoWindow
                    this.infoWindow = new qq.maps.InfoWindow({
                        map: this.qmap,
                        content: 'content',
                        position: null,
                        // zIndex
                    });
                }
            },
            resetLatlngBounds(){
                Vue.nextTick(() => {
                    let latlngBounds = new qq.maps.LatLngBounds();
                    let markers = this.manager.getMarkers();
                    markers.forEach(marker => {
                        latlngBounds.extend(marker.marker.getPosition());
                    });
                    this.qmap.fitBounds(latlngBounds);
                })
            },
        },
        created(){
            this.manager.setMap(this);
            window.initQmap = window.initQmap || this.initQmap;
            if(typeof qq == 'undefined'){
                function loadScript() {
                    let script = document.createElement("script");
                    script.type = "text/javascript";
                    script.src = "https://map.qq.com/api/js?v=2.exp&libraries=place&callback=initQmap&key=V2IBZ-JCFKG-UPXQA-IZYP3-II5B6-UFFOR";
                    document.body.appendChild(script);
                }
                loadScript();
            }else {
                this.initQmap();
            }
        },
        watch: {
            center: {
                deep: true,
                handler(val){
                    this.qmap.setCenter(new qq.maps.LatLng(this.center[1], this.center[0]))
                }
            }
        }
    }
</script>

<style scoped>
    .container {
        position: relative;
    }
</style>