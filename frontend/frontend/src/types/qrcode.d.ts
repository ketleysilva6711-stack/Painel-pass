// Type declarations for qrcode
declare module 'qrcode' {
  export type QRCodeErrorCorrectionLevel = 'L' | 'M' | 'Q' | 'H';
  export type QRCodeMaskPattern = 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7;

  export interface QRCodeOptions {
    version?: number;
    errorCorrectionLevel?: 'L' | 'M' | 'Q' | 'H';
    maskPattern?: number;
    toSJISFunc?: (codePoint: string) => number;
    width?: number;
    margin?: number;
    color?: {
      dark: string;
      light: string;
    };
  }

  export interface QRCodeToDataURLOptions extends QRCodeOptions {
    rendererOpts?: {
      quality?: number;
    };
  }

  export interface QRCodeToStringOptions extends QRCodeOptions {
    type?: 'svg' | 'terminal' | 'utf8';
  }

  export interface QRCodeToFileOptions extends QRCodeOptions {
    width?: number;
  }

  export function toDataURL(text: string, options?: QRCodeToDataURLOptions): Promise<string>;
  export function toDataURL(text: string, options: QRCodeToDataURLOptions, callback: (err: Error | null, url: string) => void): void;
  export function toString(text: string, options?: QRCodeToStringOptions): Promise<string>;
  export function toString(text: string, options: QRCodeToStringOptions, callback: (err: Error | null, string: string) => void): void;
  export function toFile(path: string, text: string, options?: QRCodeToFileOptions): Promise<void>;
  export function toFile(path: string, text: string, options: QRCodeToFileOptions, callback: (err: Error | null) => void): void;
  export function toDataURL(text: string, options?: QRCodeToDataURLOptions): Promise<string>;
  export function toFileStream(stream: NodeJS.WritableStream, text: string, options?: QRCodeOptions): Promise<void>;
}