import {Component, ElementRef, ViewChild, ViewEncapsulation} from '@angular/core';
import {AppearanceEditor} from '../appearance-editor/appearance-editor.service';
import {MenuEditor} from '../menus/menu-editor.service';
import {Menu} from '../menus/menu';
import {Modal} from '../../../core/ui/dialogs/modal.service';
import {Settings} from '../../../core/config/settings.service';
import {ConfirmModalComponent} from '../../../core/ui/confirm-modal/confirm-modal.component';
import {OverlayPanel} from '../../../core/ui/overlay-panel/overlay-panel.service';
import {AddMenuItemPanelComponent} from '../menus/add-menu-item-panel/add-menu-item-panel.component';
import {RIGHT_POSITION} from '../../../core/ui/overlay-panel/positions/right-position';

@Component({
    selector: 'menus-appearance-panel',
    templateUrl: './menus-appearance-panel.component.html',
    styleUrls: ['./menus-appearance-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class MenusAppearancePanelComponent {
    @ViewChild('addItemsBtn', {read: ElementRef}) addItemsBtn: ElementRef<HTMLButtonElement>;

    constructor(
        public appearance: AppearanceEditor,
        public menus: MenuEditor,
        private modal: Modal,
        private settings: Settings,
        private panel: OverlayPanel,
    ) {
        this.menus.setFromJson(this.settings.get('menus'));
    }

    /**
     * Toggle new menu item panel visibility.
     */
    public toggleNewMenuItemPanel() {
        const position = RIGHT_POSITION.slice();
        position[0].offsetX = 25;
        position[1].offsetX = 25;
        this.panel.open(AddMenuItemPanelComponent, {
            position: position,
            origin: this.addItemsBtn,
            panelClass: 'add-menu-item-panel-container'
        });
    }

    /**
     * Open previous appearance panel.
     */
    public openPreviousPanel() {
        // if menu panel is open, close it
        if (this.menus.activeMenu) {
            this.menus.activeMenu = null;
        }
        // otherwise navigate back to main appearance panel
        else {
            this.appearance.closeActivePanel();
        }
    }

    public setActiveMenu(menu: Menu) {
        this.menus.activeMenu = menu;
    }

    public confirmMenuDeletion() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Menu',
            body: 'Are you sure you want to delete this menu?',
            ok: 'Delete'
        }).afterClosed().subscribe(confirmed => {
            if ( ! confirmed) return;
            this.menus.deleteActive();
        });
    }

    public getDisplayName(name: string) {
        return name.replace(/-/g, ' ');
    }
}
