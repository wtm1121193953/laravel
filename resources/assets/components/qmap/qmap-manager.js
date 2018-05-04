/**
 * 地图管理工具, 用于管理一个地图实例中的各种组件
 */
export default class QmapManager{

    constructor() {
        this._map = null;
        this._markers = [];
    }
    setMap(map) {
        this._map = map;
    }
    getMap() {
        return this._map;
    }
    addMarker(marker){
        this._markers.push(marker)
    }
    setMarkers(markers){
        this._markers = markers;
    }
    getMarkers(){
        return this._markers;
    }
}