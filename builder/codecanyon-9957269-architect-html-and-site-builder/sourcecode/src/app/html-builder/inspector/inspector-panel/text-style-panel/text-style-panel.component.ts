import {Component, ElementRef, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {baseFonts, fontWeights} from "../../../text-style-values";
import {InspectorFloatingPanel} from "../../inspector-floating-panel.service";
import {GoogleFontsPanelComponent} from "./google-fonts-panel/google-fonts-panel.component";
import {SelectedElement} from "../../../live-preview/selected-element.service";
import {BuilderDocumentActions} from "../../../builder-document-actions.service";
import {OverlayPanel} from '../../../../../common/core/ui/overlay-panel/overlay-panel.service';
import {ColorpickerPanelComponent} from '../../../../../common/core/ui/color-picker/colorpicker-panel.component';
import {RIGHT_POSITION} from '../../../../../common/core/ui/overlay-panel/positions/right-position';

@Component({
    selector: 'text-style-panel',
    templateUrl: './text-style-panel.component.html',
    styleUrls: ['./text-style-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class TextStylePanelComponent implements OnInit {
    @ViewChild('googleFontsOrigin') googleFontsOrigin: ElementRef;

    public styles: any = {};

    public baseFonts = baseFonts.slice();

    public fontWeights = fontWeights.slice();

    constructor(
        private selectedElement: SelectedElement,
        private panel: InspectorFloatingPanel,
        private builderActions: BuilderDocumentActions,
        private overlayPanel: OverlayPanel,
    ) {}

    ngOnInit() {
        this.selectedElement.changed.subscribe(() => {
            this.getSelectedElementTextStyles();
        });
    }

    public applyTextStyle(name: string, addUndoCommand = true) {
        this.builderActions.applyStyle(this.selectedElement.node, name, this.styles[name], addUndoCommand);
    }

    /**
     * Toggle between specified style and "initial".
     */
    public toggleTextStyle(name: string, value: string) {
        if (this.textStyleIs(name, value)) {
            this.builderActions.applyStyle(this.selectedElement.node, name, 'initial');
        } else {
            this.builderActions.applyStyle(this.selectedElement.node, name, value);
        }
    }

    /**
     * Check if selected element's specified style equals given value.
     */
    public textStyleIs(name: string, value: string) {
        return this.selectedElement.getStyle(name).indexOf(value) > -1;
    }

    public openColorpickerPanel(origin: HTMLElement) {
        const currentColor = this.styles.color;
        this.overlayPanel.open(
            ColorpickerPanelComponent,
            {position: RIGHT_POSITION, origin: new ElementRef(origin), data: {color: currentColor}}
        ).valueChanged().subscribe(color => {
            this.styles.color = color;
            this.applyTextStyle('color', false);
        });
    }

    public openGoogleFontsPanel() {
        this.panel.open(GoogleFontsPanelComponent, this.googleFontsOrigin).selected.subscribe(fontFamily => {
            this.builderActions.applyStyle(this.selectedElement.node, 'fontFamily', fontFamily);
        });
    }

    /**
     * Get current text styles of element selected in the builder.
     */
    private getSelectedElementTextStyles() {
        this.styles = {
            color: this.selectedElement.getStyle('color'),
            fontSize: this.selectedElement.getStyle('fontSize').replace('px', ''),
            textAlign: this.selectedElement.getStyle('textAlign'),
            fontStyle: this.selectedElement.getStyle('fontStyle'),
            fontFamily: this.selectedElement.getStyle('fontFamily'),
            lineHeight: this.selectedElement.getStyle('lineHeight'),
            fontWeight: this.selectedElement.getStyle('fontWeight'),
            textDecoration: this.selectedElement.getStyle('textDecoration')
        };
    }
}
