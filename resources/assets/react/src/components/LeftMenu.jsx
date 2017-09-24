import React, {Component} from 'react';
import {Menu, Switch} from 'element-react';

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

    constructor(props){
        super(props);

        this.state = {
            currentMenu: '',
            theme: 'light',
        };
        // todo 处理当前选中的菜单

        this.onSelect = this.onSelect.bind(this);
    }

    onSelect(index){
        console.log(index);
    }

    render(){

        const menuItems = this.props.menus.map(function(subMenu){
            if(subMenu.subs && subMenu.subs.length > 0){
                let menuItems = subMenu.subs.map(function(item){
                    return <Menu.Item index={item.url} key={item.url}>{item.name}</Menu.Item>;
                });
                return <Menu.SubMenu index={subMenu.url} title={subMenu.name}>{menuItems}</Menu.SubMenu>
            }else{
                return <Menu.Item index={subMenu.url} key={subMenu.url}>{subMenu.name}</Menu.Item>;
            }
        });
        return (
            <Menu defaultActive="2" theme={this.props.theme} className="el-menu-vertical-demo" onSelect={this.onSelect}>
                {menuItems}
                <Menu.Item index="-1" key="-1">

                </Menu.Item>
            </Menu>
        );
    }
}

export default LeftMenu;