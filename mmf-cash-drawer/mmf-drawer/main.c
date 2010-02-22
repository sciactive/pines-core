/*
 * Pines MMF Cash Drawer Controller
 * Copyright (C) 2008-2009  Hunter Perrin.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Hunter can be contacted at hunter@sciactive.com
 *
 */
#include <hid.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h> /* for getopt() */

HIDInterface* hid;
hid_return ret;

int main(int argc, char *argv[]) {
    int iface_num = 0;

    unsigned short vendor_id = 0x03da;
    unsigned short product_id = 0x0100;
    char *vendor, *product;
    char action = 's';

    int flag;

    /* Parse command-line options.
     *
     * Currently, we only accept the "-d" flag, which works like "lsusb", and the
     * "-i" flag to select the interface (default 0). The syntax is one of the
     * following:
     *
     * $ test_libhid -d 1234:
     * $ test_libhid -d :5678
     * $ test_libhid -d 1234:5678
     *
     * Product and vendor IDs are assumed to be in hexadecimal.
     *
     * TODO: error checking and reporting.
     */
    while ((flag = getopt(argc, argv, "s k d:i:")) != -1) {
        switch (flag) {
            case 'd':
                product = optarg;
                vendor = strsep(&product, ":");
                if (vendor && *vendor) {
                    vendor_id = strtol(vendor, NULL, 16);
                }
                if (product && *product) {
                    product_id = strtol(product, NULL, 16);
                }
                break;
            case 'i':
                iface_num = atoi(optarg);
                break;
            case 's':
                action = 's';
                break;
            case 'k':
                action = 'k';
                break;
        }
    }

    HIDInterfaceMatcher matcher = {vendor_id, product_id, NULL, NULL, 0};

    ret = hid_init();
    if (ret != HID_RET_SUCCESS) {
        fprintf(stderr, "hid_init failed with return code %d\n", ret);
        return 1;
    }

    hid = hid_new_HIDInterface();
    if (hid == 0) {
        fprintf(stderr, "hid_new_HIDInterface() failed, out of memory?\n");
        return 1;
    }


    ret = hid_force_open(hid, iface_num, &matcher, 3);
    //ret = hid_open(hid, iface_num, &matcher);
    if (ret != HID_RET_SUCCESS) {
        fprintf(stderr, "hid_force_open failed with return code %d\n", ret);
        if (ret == HID_RET_DEVICE_NOT_FOUND)
            return 4;
        return 1;
    }


    switch (action) {
        case 's':
            if (drawer_status()) {
                printf("CLOSED\n");
                return 2;
            } else {
                printf("OPEN\n");
                return 3;
            }
            break;
        case 'k':
            if (drawer_status()) {
                drawer_kick();
                if (drawer_status()) {
                    printf("CLOSED\n");
                    return 2;
                } else {
                    printf("OPEN\n");
                    return 3;
                }
            } else {
                printf("OPEN\n");
                return 3;
            }
            break;
    }


    ret = hid_close(hid);
    if (ret != HID_RET_SUCCESS) {
        fprintf(stderr, "hid_close failed with return code %d\n", ret);
        return 1;
    }

    hid_delete_HIDInterface(&hid);

    ret = hid_cleanup();
    if (ret != HID_RET_SUCCESS) {
        fprintf(stderr, "hid_cleanup failed with return code %d\n", ret);
        return 1;
    }

    return 0;
}

int drawer_status() {
    unsigned char const PATHLEN = 3;
    int const PATH_IN[] = {0xffa000a5, 0xffa000a6, 0xffa000a7};

    unsigned char const SEND_PACKET_LEN = 2;
    char const PACKET[] = {0x0, 0x1};
    unsigned int const ENDPOINT_READ = 0x81;
    unsigned int const STATUS_LEN = 1;
    unsigned int const STATUS_TIMEOUT = 100;
    char status;

    // send packet:
    ret = hid_set_output_report(hid, PATH_IN, PATHLEN, PACKET, SEND_PACKET_LEN);
    if (ret != HID_RET_SUCCESS) {
        fprintf(stderr, "hid_set_output_report failed with return code %d\n", ret);
    }
    ret = hid_interrupt_read(hid, ENDPOINT_READ, &status, STATUS_LEN, STATUS_TIMEOUT);
    if (ret != HID_RET_SUCCESS) {
        fprintf(stderr, "hid_interrupt_read failed with return code %d\n", ret);
    }

    return (bool) status;
}

int drawer_kick() {
    unsigned char const PATHLEN = 3;
    int const PATH_IN[] = {0xffa000a5, 0xffa000a6, 0xffa000a7};

    unsigned char const SEND_PACKET_LEN = 2;
    char const PACKET[] = {0x0, 0x0};

    // send packet:
    ret = hid_set_output_report(hid, PATH_IN, PATHLEN, PACKET, SEND_PACKET_LEN);
    if (ret != HID_RET_SUCCESS) {
        fprintf(stderr, "hid_set_output_report failed with return code %d\n", ret);
        return false;
    }
    return true;
}