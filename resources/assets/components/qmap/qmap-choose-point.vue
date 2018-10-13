<template>
    <div class="container">
        <div ref="mapContainer" :style="{width: width, height: height}"></div>
        <div class="search" v-if="!disabled">
            <el-input v-model="keyword"
                      ref="autoComplete"
                      placeholder="请输入关键字"
                      suffix-icon="el-icon-search"
            />
        </div>
    </div>
</template>

<script>
    export default {
        name: "qmap-choose-point",
        props: {
            width: String,
            height: String,
            cityName: {type: String, default: '深圳'},
            center: {type: Array, default: () => [22.555086, 114.047667]},
            searchLimit: {type:Number, default: 1},
            searchable: {type: Boolean, default: true},
            disabled: {type: Boolean, default: false},
            shownMarkers: {type: Array},
        },
        data(){
            return {
                map: null, // 地图实例
                searchService: null, // 地图搜索服务实例
                autoComplete: null, // 自动补全控件
                infoWindow: null, // 信息窗体
                markers: [],
                keyword: '',
                selectedPosition: [],
                isMapLoad : false
            }
        },
        methods: {
            resetLatlngBounds(){
                let latlngBounds = new qq.maps.LatLngBounds();
                this.markers.forEach(marker => {
                    latlngBounds.extend(marker.getPosition());
                });
                this.map.fitBounds(latlngBounds);
            },
            searchComplete(results){
                let pois = results.detail.pois;
                this.markers.forEach(marker => {
                    marker.setMap(null);
                });
                this.markers = [];
                for(let i = 0,l = pois.length;i < l; i++){
                    let poi = pois[i];
                    let marker = new qq.maps.Marker({
                        map: this.map,
                        position: poi.latLng,
                        draggable: !this.disabled,
                    });
                    marker.setTitle(i+1);
                    // marker设置后设置infoWindow内容
                    qq.maps.event.addListener(marker, "click", (e) => {
                        this.infoWindow.setPosition(marker)
                        this.infoWindow.setContent(`<div class="info-window-title">${poi.name}</div><div class="info-window-address">${poi.address}</div>`)
                        this.infoWindow.open();
                    });
                    // 设置marker拖拽操作
                    qq.maps.event.addListener(marker, 'dragend', (e) => {
                        console.log(e, marker);
                        // this.infoWindow.setPosition(marker);
                        // this.infoWindow.open();
                        // this.infoWindow.setContent(`<div class="info-window-title">${poi.name}</div><div class="info-window-address">${poi.address}</div>`)
                        this.markers.push(marker);
                        this.$emit('marker-change', this.markers);
                    });
                    this.markers.push(marker);
                    this.selectedPosition = [marker.getPosition().getLng(), marker.getPosition().getLat()]
                }
                this.resetLatlngBounds();
                this.$emit('marker-change', this.markers);
            },
            addMarkerByLnglat(lnglat){
                let latLng = new qq.maps.LatLng(lnglat[1], lnglat[0]);
                let marker = new qq.maps.Marker({
                    map: this.map,
                    position: latLng,
                    draggable: !this.disabled,
                });
                // marker设置后设置infoWindow内容
                let geocoder = new qq.maps.Geocoder();
                geocoder.getAddress(latLng);
                geocoder.setComplete((result) => {
                    qq.maps.event.addListener(marker, "click", (e) => {
                        let poi = result.detail.nearPois[0] || {};
                        let address = poi.address || result.detail.address;
                        let title = poi.name || '';
                        this.infoWindow.setPosition(marker)
                        this.infoWindow.setContent(`<div class="info-window-title">${title}</div><div class="info-window-address">${address}</div>`);
                        this.infoWindow.open();
                    });
                });
                // 设置marker拖拽操作
                qq.maps.event.addListener(marker, 'dragend', (e) => {
                    console.log(e, marker);
                    // this.infoWindow.setPosition(marker);
                    // this.infoWindow.open();
                    // this.infoWindow.setContent(`<div class="info-window-title">${poi.name}</div><div class="info-window-address">${poi.address}</div>`)
                    this.markers.push(marker);
                    this.$emit('marker-change', this.markers);
                });

                this.markers.push(marker);
                this.resetLatlngBounds();
            },
            loadMap(){
                let initQmap = () => {
                    // 初始化地图
                    let center = this.center;
                    this.map = new qq.maps.Map(this.$refs.mapContainer, {
                        center: new qq.maps.LatLng(...center), // 地图的中心地理坐标。
                        zoom: 10, // 缩放级别
                        zoomControl: false, // 缩放控件
                        panControl: false, // 平移控件
                        mapTypeControl: false, // 地图类型切换
                    });
                    if(this.searchable && !this.disabled){
                        // 初始化搜索服务
                        let SearchOptions = {
                            // map: this.map,
                            complete: (results) => {this.searchComplete(results)},
                            pageCapacity: this.searchLimit,
                            location: this.cityName,
                        };
                        this.searchService=new qq.maps.SearchService(SearchOptions);
                        // 初始化信息窗口
                        this.infoWindow = new qq.maps.InfoWindow({
                            map: this.map,
                            content: 'content',
                            position: null,
                            // zIndex
                        });
                        // 初始化自动补全控件 腾讯地图的自动补全zindex为1, 且无法修改, 在弹框中显示不出来
                        this.autoComplete = new qq.maps.place.Autocomplete(this.$refs.autoComplete.$refs.input, {
                            location: this.cityName,
                        });
                        //添加监听事件
                        qq.maps.event.addListener(this.autoComplete, "confirm", (res) => {
                            this.searchService.search(res.value);
                        });
                    }
                    // 回显marker
                    if(this.shownMarkers){
                        this.shownMarkers.forEach(item => {
                            if(item && item.length == 2){
                                this.addMarkerByLnglat(item);
                            }
                        })
                    }
                };
                window.initQmap = window.initQmap || initQmap;

                if(typeof qq == 'undefined'){
                    function loadScript() {
                        var script = document.createElement("script");
                        script.type = "text/javascript";
                        script.src = "https://map.qq.com/api/js?v=2.exp&libraries=place&callback=initQmap&key=V2IBZ-JCFKG-UPXQA-IZYP3-II5B6-UFFOR";
                        document.body.appendChild(script);
                    }
                    loadScript();
                }else {
                    initQmap();
                }
            }
        },
        created(){
        },

        mounted(){
            this.loadMap();
        },
        watch: {
            shownMarkers(val){
                this.addMarkerByLnglat(val)
            },
            center(val){
                this.map.setCenter(...val)
            },
            cityName(val){
                this.searchService.setLocation(val);
                this.autoComplete && this.autoComplete.dispose();
                this.autoComplete = new qq.maps.place.Autocomplete(this.$refs.autoComplete.$refs.input, {
                    location: val
                });
            }
        }
    }
</script>

<style scoped>
    .container {
        position: relative;
    }
    .search {
        position: absolute;
        top: 10px;
        left: 10px;
        /*width: 100%;*/
    }
</style>
<style>
    .info-window-title {
        font-size: 18px;
        color: #5bb1fa;
    }
</style>