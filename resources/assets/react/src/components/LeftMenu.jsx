import React, {Component} from 'react';
import {Menu} from 'element-react';

class LeftMenu extends Component {
    constructor(props){
        super(props);
        this.menus = [
            {id: 1, name: '权限管理', pid: 0, url: 'auth', subMenu: [
                {id: 2, name: '用户管理', pid: 1, url: 'user/list'},
                {id: 3, name: '角色管理', pid: 1, url: 'group/list'},
                {id: 4, name: '权限管理', pid: 1, url: 'rule/list'},
            ]},
        ];
    }
    onSelect(e){
        console.log(e);
    }

    render(){
        return (
            <Menu theme={this.props.theme} style={{minHeight: '400px'}} onSelect={this.onSelect.bind(this)}>
                {this.menus.map(function(v, k){
                    if(v.subMenu && v.subMenu.length > 0){
                        return <Menu.SubMenu key={v.url} index={v.url} title={<span>{v.name}</span>}>
                            {v.subMenu.map(function(subV, subK){
                                return <Menu.Item key={subV.url} index={subV.url}>{subV.name}</Menu.Item>
                            })}
                        </Menu.SubMenu>
                    }else{
                        return <Menu.Item key={v.url} index={v.url}>v.name</Menu.Item>
                    }
                })}
            </Menu>
        );
    }
}

export default LeftMenu;