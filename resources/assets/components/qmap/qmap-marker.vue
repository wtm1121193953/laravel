<template>
    <div>
        <slot/>
    </div>
</template>

<script>
    /**
     * 腾讯地图marker组件
     */
    export default {
        name: "qmap-marker",
        props: {
            value: {type: Array,}, // marker的值, 一个数组, 第一个元素是经度, 第二个元素是纬度
            draggable: {type: Boolean, default: false}, // marker是否可拖拽, 默认不可拖拽
            showInfoWindow: {type: Boolean, default: true}, // 是否显示信息窗体
        },
        inject: ['manager'],
        data(){
            return {
                marker: null, // marker实例
            }
        },
        computed: {
            map(){
                return this.manager.getMap();
            },
            qmap(){
                return this.map.qmap;
            }
        },
        methods: {
            init(){
                // 初始化操作
                if(!this.qmap){
                    // 如果地图尚未加载完成, 延迟100毫秒后再执行
                    setTimeout(() => {this.init()}, 100)
                }else {
                    let latLng = new qq.maps.LatLng(this.value[1], this.value[0]);
                    this.marker = new qq.maps.Marker({
                        map: this.qmap,
                        position: latLng,
                        draggable: this.draggable,
                    });
                    if(this.draggable){
                        // 设置marker拖拽操作结束的事件
                        qq.maps.event.addListener(this.marker, 'dragend', (e) => {
                            let value = [this.marker.getPosition().getLng(), this.marker.getPosition().getLat()];
                            this.$emit('change', value);
                            this.$emit('input', value)
                            if(this.showInfoWindow){
                                this.changeInfoWindow();
                            }
                        });
                    }
                    if(this.showInfoWindow){
                        // 监听marker点击事件, 点击时展示信息窗口
                        qq.maps.event.addListener(this.marker, "click", (e) => {
                            // 点击时将当前标注置位最顶层
                            let zindex = 0;
                            this.manager.getMarkers().forEach(marker => {
                                zindex = Math.max(marker.marker.getZIndex(), zindex)
                            });
                            this.marker.setZIndex(zindex + 1);
                            this.changeInfoWindow();
                        });
                    }
                    if(this.map.autoBoundsOnMarker){
                        // 初始化成功重置地图边界
                        this.map.resetLatlngBounds();
                    }
                }
            },
            changeInfoWindow(){
                // marker设置后设置infoWindow内容
                let geocoder = new qq.maps.Geocoder();
                geocoder.getAddress(this.marker.getPosition());
                geocoder.setComplete((result) => {
                    let poi = result.detail.nearPois[0] || {};
                    let address = poi.address || result.detail.address;
                    let title = poi.name || '';
                    this.map.infoWindow.setPosition(this.marker);
                    this.map.infoWindow.setContent(`<div class="info-window-title">${title}</div><div class="info-window-address">${address}</div>`);
                    this.map.infoWindow.open();
                });
            }
        },
        created(){
            // 将marker加入到qmap-manager中管理
            this.manager.addMarker(this);
            this.init();
        },
        watch: {
            value: {
                deep: true,
                handler(val){
                    let latLng = new qq.maps.LatLng(this.value[1], this.value[0]);
                    this.marker.setPosition(latLng);
                }
            }
        }
    }
</script>
<style>
    .info-window-title {
        font-size: 18px;
        color: #5bb1fa;
    }
</style>
<style scoped>

</style>