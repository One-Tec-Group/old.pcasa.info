import {Component, ViewEncapsulation} from '@angular/core';
import {SettingsPanelComponent} from 'common/admin/settings/settings-panel.component';

@Component({
    selector: 'builder-settings',
    templateUrl: './builder-settings.component.html',
    styleUrls: ['./builder-settings.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class BuilderSettingsComponent extends SettingsPanelComponent {

    public categories: string[] = [];

    ngOnInit() {
        this.categories = this.settings.getJson('builder.template_categories', []);
    }

    public addCategory(value: string) {
        const i = this.categories.findIndex(card => card === value);
        if ( ! value || i > -1) return;
        this.categories.push(value);
    }

    public removeCategory(value: string) {
        const i = this.categories.findIndex(card => card === value);
        this.categories.splice(i, 1);
    }

    /**
     * Save current settings to the server.
     */
    public saveSettings() {
        const settings = this.state.getModified();
        settings.client['builder.template_categories'] = JSON.stringify(this.categories);
        super.saveSettings(settings);
    }
}
