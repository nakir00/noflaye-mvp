import { PageProps } from '@/types';
import AppLayout from '@/Layouts/AppLayout';

export default function Home({ auth }: PageProps) {
    return (
        <AppLayout auth={auth}>
            <div className="flex min-h-screen flex-col items-center justify-center">
                <h1 className="text-4xl font-bold">Welcome to Noflaye Box</h1>
                <p className="mt-4 text-muted-foreground">
                    Food Delivery Platform - Senegal
                </p>
            </div>
        </AppLayout>
    );
}
