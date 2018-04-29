<template>
    <div>

        <div :id="mapId" class="container" :style="{width: width, height: height}">

        </div>
        <div id="amap"></div>
    </div>
</template>

<script>
    export default {
        name: "qmap",
        props: {
            width: String,
            height: String,
        },
        data(){
            return {
                mapId: 'qmap_' + parseInt(Math.random() * 1000000)
            }
        },
        created(){
        },

        mounted(){
            console.log(this.mapId, document.getElementById(this.mapId), document.getElementById('amap'))

            window.initQmap = window.initQmap || function(){
                var map = new qq.maps.Map(document.getElementById(this.mapId), {
                    center: new qq.maps.LatLng(39.916527,116.397128),      // 地图的中心地理坐标。
                    zoom:10                                                 // 地图的中心地理坐标。
                });
            };
            if(typeof qq == 'undefined'){
                function loadScript() {
                    var script = document.createElement("script");
                    script.type = "text/javascript";
                    script.src = "http://map.qq.com/api/js?v=2.exp&callback=initQmap";
                    document.body.appendChild(script);
                }
                loadScript();
            }
        },
    }
</script>

<style scoped>
    .container {
        width: 100%;
        height: 100%;
    }
</style>