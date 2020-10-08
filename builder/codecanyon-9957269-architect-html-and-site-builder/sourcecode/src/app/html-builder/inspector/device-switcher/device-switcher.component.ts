import {Component, HostBinding, ViewChild, ViewEncapsulation} from '@angular/core';
import {state, style, animate, transition, trigger} from "@angular/animations";
import {MatTabChangeEvent, MatTabGroup} from "@angular/material";
import {LivePreview} from "../../live-preview.service";
import {ContextBoxes} from "../../live-preview/context-boxes.service";

@Component({
    selector: 'device-switcher',
    templateUrl: './device-switcher.component.html',
    styleUrls: ['./device-switcher.component.scss'],
    encapsulation: ViewEncapsulation.None,
    animations: [
        trigger('toggleAnimation', [
            state('true', style({
                height: '*',
                display: 'block',
            })),
            state('false', style({
                height: '0',
                display: 'none'
            })),
            transition('true <=> false', animate('225ms cubic-bezier(.4,0,.2,1)'))
        ])
    ]
})
export class DeviceSwitcherComponent {
    @ViewChild('tabs') tabs: MatTabGroup;

    /**
     * Whether device switcher is visible.
     */
    @HostBinding('@toggleAnimation') private visible: boolean = false;

    /**
     * Currently selected device switcher tab.
     */
    private selectedIndex: number = 3;

    /**
     * DeviceSwitcherComponent Constructor.
     */
    constructor(
        private livePreview: LivePreview,
        private contextBoxes: ContextBoxes,
    ) {}

    /**
     * Toggle visibility of device switcher.
     */
    public toggleVisibility() {
        this.visible = !this.visible;

        if (this.visible) {
            requestAnimationFrame(() => this.tabs.selectedIndex = this.selectedIndex);
        }
    }

    /**
     * Change live preview width based on selected tab.
     */
    public switchDevice(e: MatTabChangeEvent) {
        this.selectedIndex = e.index;
        this.livePreview.setWidth(this.getWidthFromIndex(e.index));
        this.contextBoxes.hideBoxes();
    }

    /**
     * Get width for live preview from specified tab index.
     */
    private getWidthFromIndex(index: number): any {
        switch (index) {
            case 0: return 'phone';
            case 1: return 'tablet';
            case 2: return 'laptop';
            case 3: return 'desktop';
        }
    }
}
