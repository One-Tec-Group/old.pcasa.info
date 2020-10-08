import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {MenuItem} from '../menu-item';
import {MenuEditor} from '../menu-editor.service';
import {AppearanceEditor} from '../../appearance-editor/appearance-editor.service';
import {Page} from '../../../../core/types/models/Page';
import {Pages} from '../../../../core/pages/pages.service';
import {OverlayPanelRef} from '../../../../core/ui/overlay-panel/overlay-panel-ref';

@Component({
    selector: 'add-menu-item-panel',
    templateUrl: './add-menu-item-panel.component.html',
    styleUrls: ['./add-menu-item-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AddMenuItemPanelComponent implements OnInit {
    public linkModel: {url?: string, linkText?: string} = {};
    public activePanel: string;
    public allPages: Page[];

    constructor(
        public editor: MenuEditor,
        public appearance: AppearanceEditor,
        private pages: Pages,
        private overlayPanelRef: OverlayPanelRef,
    ) {}

    ngOnInit() {
        this.pages.getAll().subscribe(response => this.allPages = response.pagination.data);
    }

    /**
     * Toggle specified menu items panel.
     */
    public togglePanel(name: string) {
        this.activePanel = this.activePanel === name ? null : name;
    }

    /**
     * Add a new link item to currently active menu.
     */
    public addLinkMenuItem() {
        this.editor.addItem(new MenuItem({
            type: 'link',
            label: this.linkModel.linkText,
            action: this.linkModel.url,
        }));

        this.linkModel = {};
        this.close();
    }

    /**
     * Add a new route item to currently active menu.
     */
    public addRouteMenuItem(route: string) {
        this.editor.addItem(new MenuItem({
            type: 'route',
            label: route,
            action: route,
        }));
        this.close();
    }

    /**
     * Add a new page item to currently active menu.
     */
    public addPageMenuItem(page: Page) {
        this.editor.addItem(new MenuItem({
            type: 'page',
            label: page.slug,
            action: page.id + '/' + page.slug,
        }));
        this.close();
    }

    public close() {
        this.overlayPanelRef.close();
    }
}
