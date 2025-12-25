import { usePage } from '@inertiajs/react';
import { Fragment } from 'react';
import { Menu, Transition } from '@headlessui/react';
import {
    ChevronDownIcon,
    ShieldCheckIcon,
    BuildingStorefrontIcon,
    FireIcon,
    TruckIcon,
    CubeIcon,
    EyeIcon,
} from '@heroicons/react/24/outline';

interface Entity {
    id: number;
    name: string;
    url: string;
    linked_shops?: string[];
    linked_kitchens?: string[];
    linked_drivers?: string[];
}

interface Panel {
    id: string;
    name: string;
    url: string;
    icon: string;
    color: string;
    entities: Entity[];
}

const iconMap: Record<string, any> = {
    'heroicon-o-shield-check': ShieldCheckIcon,
    'heroicon-o-building-storefront': BuildingStorefrontIcon,
    'heroicon-o-fire': FireIcon,
    'heroicon-o-truck': TruckIcon,
    'heroicon-o-cube': CubeIcon,
    'heroicon-o-eye': EyeIcon,
};

const colorMap: Record<string, string> = {
    danger: 'text-red-600',
    primary: 'text-blue-600',
    warning: 'text-orange-600',
    success: 'text-green-600',
    info: 'text-cyan-600',
    purple: 'text-purple-600',
};

export default function PanelSwitcher() {
    const { accessible_panels } = usePage().props as { accessible_panels: Panel[] };

    if (!accessible_panels || accessible_panels.length === 0) {
        return null;
    }

    return (
        <Menu as="div" className="relative inline-block text-left">
            <Menu.Button className="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Switch Panel
                <ChevronDownIcon className="-mr-1 h-5 w-5 text-gray-400" />
            </Menu.Button>

            <Transition
                as={Fragment}
                enter="transition ease-out duration-100"
                enterFrom="transform opacity-0 scale-95"
                enterTo="transform opacity-100 scale-100"
                leave="transition ease-in duration-75"
                leaveFrom="transform opacity-100 scale-100"
                leaveTo="transform opacity-0 scale-95"
            >
                <Menu.Items className="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 max-h-96 overflow-y-auto focus:outline-none">
                    <div className="py-1">
                        {accessible_panels.map((panel) => {
                            const Icon = iconMap[panel.icon] || ShieldCheckIcon;
                            const colorClass = colorMap[panel.color] || 'text-gray-600';

                            return (
                                <div key={panel.id} className="border-b border-gray-100 last:border-b-0">
                                    <Menu.Item>
                                        {({ active }) => (
                                            <a
                                                href={panel.url}
                                                className={`${
                                                    active ? 'bg-gray-100' : ''
                                                } flex items-center px-4 py-3 text-sm font-medium`}
                                            >
                                                <Icon className={`mr-3 h-5 w-5 ${colorClass}`} />
                                                <span className="text-gray-900">{panel.name}</span>
                                            </a>
                                        )}
                                    </Menu.Item>

                                    {panel.entities && panel.entities.length > 0 && (
                                        <div className="pl-8 pb-2 space-y-1 bg-gray-50">
                                            {panel.entities.map((entity) => (
                                                <Menu.Item key={entity.id}>
                                                    {({ active }) => (
                                                        <div>
                                                            <a
                                                                href={entity.url}
                                                                className={`${
                                                                    active ? 'bg-gray-200' : 'bg-gray-50'
                                                                } block px-4 py-2 text-xs rounded hover:bg-gray-200 transition-colors`}
                                                            >
                                                                <span className="font-medium text-gray-900">
                                                                    {entity.name}
                                                                </span>

                                                                {/* Linked entities */}
                                                                {(entity.linked_shops?.length ||
                                                                  entity.linked_kitchens?.length ||
                                                                  entity.linked_drivers?.length) && (
                                                                    <div className="mt-1 text-xs text-gray-500 space-y-0.5">
                                                                        {entity.linked_shops && entity.linked_shops.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🏪</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_shops.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                        {entity.linked_kitchens && entity.linked_kitchens.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🔥</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_kitchens.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                        {entity.linked_drivers && entity.linked_drivers.length > 0 && (
                                                                            <div className="flex items-start gap-1">
                                                                                <span>🚚</span>
                                                                                <span className="line-clamp-1">
                                                                                    {entity.linked_drivers.join(', ')}
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                    </div>
                                                                )}
                                                            </a>
                                                        </div>
                                                    )}
                                                </Menu.Item>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </Menu.Items>
            </Transition>
        </Menu>
    );
}
