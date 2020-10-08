import {ComponentRef, ElementRef, Injectable} from '@angular/core';
import {Overlay, OverlayRef} from "@angular/cdk/overlay";
import {CodeEditorComponent} from "./code-editor.component";
import {ComponentPortal} from "@angular/cdk/portal";
import {Observable} from "rxjs";

@Injectable()
export class CodeEditor {

    /**
     * Reference to code editor overlay.
     */
    public overlayRef: OverlayRef;

    /**
     * Origin element for code editor overlay.
     */
    private origin: ElementRef;

    private componentRef: ComponentRef<CodeEditorComponent>;

    /**
     * CodeEditor Constructor.
     */
    constructor(private overlay: Overlay) {}

    public toggle() {
        if (this.overlayRef) {
            this.close();
        } else {
            this.open();
        }
    }

    public open(): Observable<CodeEditorComponent> {
        if (this.overlayRef) return this.componentRef.instance.afterLoaded();

        const strategy = this.overlay.position().connectedTo(
            this.origin,
            {originX: 'end', originY: 'bottom'},
            {overlayX: 'start', overlayY: 'bottom'}
        ).withFallbackPosition(
            {originX: 'end', originY: 'top'},
            {overlayX: 'start', overlayY: 'top'}
        ).withOffsetX(15).withOffsetY(15);

        if (this.overlayRef) this.overlayRef.dispose();

        const rect = document.querySelector('live-preview').getBoundingClientRect();
        const width = rect.width - 50;
        const height = rect.height - 30;

        this.overlayRef = this.overlay.create({positionStrategy: strategy, width, height});
        this.componentRef = this.overlayRef.attach(new ComponentPortal(CodeEditorComponent)) as ComponentRef<CodeEditorComponent>;

        this.componentRef.instance.onClose().subscribe(() => {
            this.close();
        });

        return this.componentRef.instance.afterLoaded();
    }

    public close() {
        if ( ! this.overlayRef) return;
        this.overlayRef.dispose();
        this.overlayRef = null;
    }

    public setOrigin(origin: ElementRef) {
        this.origin = origin;
    }
}
