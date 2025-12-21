import { PropsWithChildren } from 'react';
import { PageProps } from '@/types';

export default function AppLayout({ children }: PropsWithChildren<{ auth?: PageProps['auth'] }>) {
    return (
        <div className="min-h-screen bg-background">
            <main>{children}</main>
        </div>
    );
}
