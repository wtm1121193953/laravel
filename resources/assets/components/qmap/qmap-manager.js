export default class QmapManager{

    constructor() {
        this._map = null;
    }
    setMap(map) {
        this._map = map;
    }
    getMap() {
        return this._map;
    }
}